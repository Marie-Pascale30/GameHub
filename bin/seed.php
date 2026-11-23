<?php

declare(strict_types=1);

/*
 * Remplit la base avec les données de démonstration : catalogue, comptes de démo,
 * joueurs fictifs, favoris et avis. Déterministe (graine fixe).
 * Usage : php bin/seed.php [--if-empty]
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();
$gamesCount = (int) $pdo->query('SELECT COUNT(*) FROM games')->fetchColumn();
if ($gamesCount > 0) {
    if (in_array('--if-empty', $argv, true)) {
        echo 'Données déjà présentes : seed ignoré.' . PHP_EOL;
        exit(0);
    }
    fwrite(STDERR, 'La base contient déjà des données. Supprimez-la ou utilisez --if-empty.' . PHP_EOL);
    exit(1);
}

$data = require BASE_PATH . '/database/seeds/catalog.php';
mt_srand(2026);
$now = time();
$at = static fn (int $daysAgo, int $jitter = 0): string => date('Y-m-d H:i:s', $now - $daysAgo * 86400 - $jitter);

function slugify(string $text): string
{
    $text = strtr(mb_strtolower($text), ['à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c']);
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', $text), '-');
}

$pdo->beginTransaction();

// Genres et plateformes
$genreIds = [];
$stmt = $pdo->prepare('INSERT INTO genres (name, slug) VALUES (?, ?)');
foreach ($data['genres'] as $name) {
    $stmt->execute([$name, slugify($name)]);
    $genreIds[$name] = (int) $pdo->lastInsertId();
}
$platformIds = [];
$stmt = $pdo->prepare('INSERT INTO platforms (name, slug, short_name, sort_order) VALUES (?, ?, ?, ?)');
foreach ($data['platforms'] as $i => [$name, $slug, $short]) {
    $stmt->execute([$name, $slug, $short, $i]);
    $platformIds[$slug] = (int) $pdo->lastInsertId();
}

// Jeux
$gameIds = [];
$insertGame = $pdo->prepare('INSERT INTO games (title, developer, release_year, synopsis, created_at) VALUES (?, ?, ?, ?, ?)');
$insertGenre = $pdo->prepare('INSERT INTO game_genre (game_id, genre_id) VALUES (?, ?)');
$insertPlatform = $pdo->prepare('INSERT INTO game_platform (game_id, platform_id, position) VALUES (?, ?, ?)');
foreach ($data['games'] as $i => [$title, $year, $developer, $genres, $platforms, $synopsis]) {
    $insertGame->execute([$title, $developer, $year, $synopsis, $at(200 - $i)]);
    $id = (int) $pdo->lastInsertId();
    $gameIds[$title] = $id;
    foreach ($genres as $g) {
        $insertGenre->execute([$id, $genreIds[$g]]);
    }
    foreach ($platforms as $pos => $p) {
        $insertPlatform->execute([$id, $platformIds[$p], $pos]);
    }
}

// Comptes
$insertUser = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)');
$demoHash = password_hash('password', PASSWORD_DEFAULT);
$users = [
    ['admin', 'admin@gamehub.fr', $demoHash, 'admin', 240],
    ['joueur', 'joueur@gamehub.fr', $demoHash, 'user', 95],
];
foreach (['lina_pixel', 'theo.speedrun', 'sarah_rpg', 'kenji64', 'manon-indie', 'hugo_retro', 'yasmine.fps'] as $k => $name) {
    // Joueurs fictifs : mot de passe aléatoire, comptes non connectables.
    $users[] = [$name, slugify($name) . '@exemple.fr', password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), 'user', 180 - $k * 12];
}
$userIds = [];
foreach ($users as [$name, $email, $hash, $role, $days]) {
    $insertUser->execute([$name, $email, $hash, $role, $at($days)]);
    $userIds[$name] = (int) $pdo->lastInsertId();
}

$texts = [
    5 => [
        'Un chef-d’œuvre, tout simplement. J’y reviens presque chaque année.',
        'La direction artistique et la musique m’ont scotché du début à la fin.',
        'Le genre de jeu qui donne envie d’en parler à tout le monde.',
        'Fini trois fois, et je découvre encore des secrets.',
        'Une claque. Le rythme est parfait, aucun temps mort.',
        'Un classique indispensable qui n’a pas pris une ride.',
    ],
    4 => [
        'Très bon jeu, quelques longueurs en milieu de partie mais rien de grave.',
        'Excellent, même si la prise en main demande un petit temps d’adaptation.',
        'Une belle aventure, j’aurais juste aimé un peu plus de contenu.',
        'Des sensations au top et une difficulté bien dosée.',
        'Très solide. L’histoire m’a vraiment surpris.',
    ],
    3 => [
        'Sympa, mais il a un peu vieilli à mon goût.',
        'Bonne idée de départ, exécution inégale.',
        'Agréable à petites doses, je décroche sur la durée.',
        'Pas mal du tout, mais je n’ai pas accroché à l’univers.',
    ],
    2 => [
        'Trop frustrant pour moi, j’ai abandonné au bout de quelques heures.',
        'Je comprends le culte, mais ce n’est vraiment pas mon style.',
    ],
    1 => ['Pas pour moi : maniabilité trop rigide.'],
];

$insertReview = $pdo->prepare('INSERT INTO reviews (user_id, game_id, rating, body, created_at) VALUES (?, ?, ?, ?, ?)');
$insertFavorite = $pdo->prepare('INSERT INTO favorites (user_id, game_id, created_at) VALUES (?, ?, ?)');

// Joueurs fictifs : avis et favoris pseudo-aléatoires mais reproductibles
$titles = array_keys($gameIds);
$quality = [];
foreach ($titles as $t) {
    $quality[$t] = 3.4 + mt_rand(0, 14) / 10; // de 3,4 à 4,8
}
foreach (['lina_pixel', 'theo.speedrun', 'sarah_rpg', 'kenji64', 'manon-indie', 'hugo_retro', 'yasmine.fps'] as $name) {
    $uid = $userIds[$name];
    $pool = $titles;
    shuffle($pool);
    foreach (array_slice($pool, 0, mt_rand(9, 16)) as $t) {
        $rating = (int) max(1, min(5, round($quality[$t] + mt_rand(-12, 8) / 10)));
        $body = mt_rand(1, 100) <= 70 ? $texts[$rating][array_rand($texts[$rating])] : null;
        $insertReview->execute([$uid, $gameIds[$t], $rating, $body, $at(mt_rand(3, 120), mt_rand(0, 80000))]);
    }
    shuffle($pool);
    foreach (array_slice($pool, 0, mt_rand(4, 10)) as $t) {
        $insertFavorite->execute([$uid, $gameIds[$t], $at(mt_rand(2, 150), mt_rand(0, 80000))]);
    }
}

// Compte de démo « joueur » : un profil déjà vivant
foreach ([
    ['The Legend of Zelda: Breath of the Wild', 18],
    ['Hollow Knight', 12],
    ['Celeste', 9],
    ['Hades', 5],
    ['Chrono Trigger', 2],
] as [$t, $days]) {
    $insertFavorite->execute([$userIds['joueur'], $gameIds[$t], $at($days, 3600)]);
}
foreach ([
    ['Hollow Knight', 5, 'Exploration incroyable, chaque recoin d’Hallownest cache quelque chose. Les boss de fin sont exigeants mais tellement gratifiants.', 11],
    ['Celeste', 5, 'La plateforme la plus précise que j’aie jouée, et une histoire qui m’a beaucoup touché.', 8],
    ['Minecraft', 4, 'Des centaines d’heures en coop avec mes amis. Le mode survie reste mon préféré.', 6],
    ['Pac-Man', 3, null, 4],
] as [$t, $rating, $body, $days]) {
    $insertReview->execute([$userIds['joueur'], $gameIds[$t], $rating, $body, $at($days, 7200)]);
}

$pdo->commit();

printf(
    "✔ Seed terminé : %d jeux, %d genres, %d plateformes, %d comptes.\n  Démo : joueur@gamehub.fr / password — admin@gamehub.fr / password\n",
    count($gameIds), count($genreIds), count($platformIds), count($userIds)
);
