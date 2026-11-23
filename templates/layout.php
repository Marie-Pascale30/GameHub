<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;

$currentUser = Auth::user();
$flashes = Session::pullFlashes();
$path = Request::path();
$active = static fn (string $prefix): string => ($prefix === '/' ? $path === '/' : str_starts_with($path, $prefix)) ? ' is-active' : '';
?>
<!doctype html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Catalogue') ?> · GameHub</title>
    <meta name="description" content="GameHub — catalogue communautaire de jeux vidéo : coups de cœur, notes et avis de joueurs.">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="color-scheme" content="dark light">
    <meta name="theme-color" content="#0a0c16">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="<?= e(asset('js/theme.js')) ?>"></script>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#contenu">Aller au contenu</a>

<header class="topbar">
    <div class="container topbar__inner">
        <a class="brand" href="/" aria-label="GameHub — accueil">
            <span class="brand__logo"><?= icon('gamepad') ?></span>
            <span class="brand__name">Game<strong>Hub</strong></span>
        </a>

        <nav class="nav" aria-label="Navigation principale">
            <a class="nav__link<?= $active('/') ?><?= $active('/jeu') ?>" href="/"><?= icon('layers') ?><span>Catalogue</span></a>
            <?php if ($currentUser): ?>
                <a class="nav__link<?= $active('/favoris') ?>" href="/favoris"><?= icon('heart') ?><span>Coups de cœur</span></a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a class="nav__link<?= $active('/admin') ?>" href="/admin/jeux"><?= icon('shield') ?><span>Admin</span></a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="topbar__actions">
            <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Basculer le thème clair / sombre" title="Thème clair / sombre">
                <?= icon('sun', 'icon icon--sun') ?><?= icon('moon', 'icon icon--moon') ?>
            </button>
            <?php if ($currentUser): ?>
                <a class="user-chip<?= $active('/profil') ?>" href="/profil" title="Mon profil">
                    <span class="avatar avatar--sm avatar--t<?= avatar_tone($currentUser['username']) ?>"><?= e(initials($currentUser['username'])) ?></span>
                    <span class="user-chip__name"><?= e($currentUser['username']) ?></span>
                </a>
                <form method="post" action="/deconnexion" class="inline-form">
                    <?= csrf_field() ?>
                    <button class="icon-btn" type="submit" aria-label="Se déconnecter" title="Se déconnecter"><?= icon('logout') ?></button>
                </form>
            <?php else: ?>
                <a class="btn btn--ghost btn--sm" href="/connexion">Connexion</a>
                <a class="btn btn--primary btn--sm hide-xs" href="/inscription">Créer un compte</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main id="contenu" class="main">
    <?php if ($flashes): ?>
        <div class="container flashes">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?>" role="status">
                    <?= icon($flash['type'] === 'error' ? 'info' : ($flash['type'] === 'success' ? 'check' : 'info')) ?>
                    <span><?= e($flash['message']) ?></span>
                    <button class="flash__close" type="button" data-dismiss aria-label="Fermer"><?= icon('x') ?></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="footer">
    <div class="container footer__inner">
        <p><strong>GameHub v2</strong> — réécriture personnelle (2026) d’un projet de BTS SIO.</p>
        <p class="footer__muted">Jaquettes générées à partir du titre : aucune image protégée n’est utilisée. Les marques citées appartiennent à leurs propriétaires.</p>
    </div>
</footer>

<div class="toasts" data-toasts aria-live="polite" aria-atomic="true"></div>
</body>
</html>
