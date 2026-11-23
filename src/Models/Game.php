<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Game extends Model
{
    public const PER_PAGE = 12;

    /** Tri : liste blanche, jamais de concaténation d'une saisie utilisateur. */
    public const SORTS = [
        'populaire' => ['label' => 'Les plus populaires', 'sql' => 'COALESCE(f.fav_count, 0) DESC, r.avg_rating DESC, g.title ASC'],
        'note'      => ['label' => 'Mieux notés',         'sql' => 'CASE WHEN r.avg_rating IS NULL THEN 1 ELSE 0 END, r.avg_rating DESC, COALESCE(r.review_count, 0) DESC, g.title ASC'],
        'recent'    => ['label' => 'Plus récents',        'sql' => 'g.release_year DESC, g.title ASC'],
        'ancien'    => ['label' => 'Plus anciens',        'sql' => 'g.release_year ASC, g.title ASC'],
        'titre'     => ['label' => 'Titre (A → Z)',       'sql' => 'g.title ASC'],
    ];

    private const SELECT = 'SELECT g.id, g.title, g.developer, g.release_year, g.synopsis, g.created_at,
            r.avg_rating, COALESCE(r.review_count, 0) AS review_count, COALESCE(f.fav_count, 0) AS fav_count
        FROM games g
        LEFT JOIN (SELECT game_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews GROUP BY game_id) r ON r.game_id = g.id
        LEFT JOIN (SELECT game_id, COUNT(*) AS fav_count FROM favorites GROUP BY game_id) f ON f.game_id = g.id';

    /**
     * Recherche + filtres + tri + pagination.
     * @return array{items:list<array>, total:int, page:int, pages:int}
     */
    public function search(array $filters, int $page = 1, int $perPage = self::PER_PAGE): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->scalar('SELECT COUNT(*) FROM games g' . $whereSql, $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $order = self::SORTS[$filters['sort'] ?? '']['sql'] ?? self::SORTS['populaire']['sql'];
        $items = $this->all(self::SELECT . $whereSql . ' ORDER BY ' . $order . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset, $params);

        return ['items' => $this->hydrate($items), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    private function buildWhere(array $f): array
    {
        $where = [];
        $params = [];

        if (($f['q'] ?? '') !== '') {
            $like = '%' . strtr(mb_strtolower($f['q']), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $where[] = "(LOWER(g.title) LIKE ? ESCAPE '!' OR LOWER(g.developer) LIKE ? ESCAPE '!')";
            array_push($params, $like, $like);
        }
        if (($f['genre'] ?? '') !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM game_genre gg JOIN genres ge ON ge.id = gg.genre_id WHERE gg.game_id = g.id AND ge.slug = ?)';
            $params[] = $f['genre'];
        }
        if (($f['platform'] ?? '') !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM game_platform gp JOIN platforms p ON p.id = gp.platform_id WHERE gp.game_id = g.id AND p.slug = ?)';
            $params[] = $f['platform'];
        }
        if (!empty($f['decade'])) {
            $where[] = 'g.release_year BETWEEN ? AND ?';
            array_push($params, (int) $f['decade'], (int) $f['decade'] + 9);
        }

        return [$where, $params];
    }

    public function find(int $id): ?array
    {
        $game = $this->one(self::SELECT . ' WHERE g.id = ?', [$id]);
        return $game ? $this->hydrate([$game])[0] : null;
    }

    /** Jeux partageant au moins un genre, les mieux notés d'abord. */
    public function similar(array $game, int $limit = 4): array
    {
        $genreIds = array_column($game['genres'], 'id');
        if (!$genreIds) {
            return [];
        }
        $rows = $this->all(
            self::SELECT . ' WHERE g.id <> ? AND g.id IN (SELECT game_id FROM game_genre WHERE genre_id IN (' . Database::placeholders(count($genreIds)) . '))
             ORDER BY r.avg_rating DESC, COALESCE(f.fav_count, 0) DESC LIMIT ' . (int) $limit,
            [$game['id'], ...$genreIds]
        );
        return $this->hydrate($rows);
    }

    /** Ajoute genres et plateformes (2 requêtes pour toute la liste, pas de N+1). */
    public function hydrate(array $games): array
    {
        if (!$games) {
            return [];
        }
        $ids = array_map(static fn ($g) => (int) $g['id'], $games);
        $in = Database::placeholders(count($ids));

        $genres = [];
        foreach ($this->all("SELECT gg.game_id, ge.id, ge.name, ge.slug FROM game_genre gg JOIN genres ge ON ge.id = gg.genre_id WHERE gg.game_id IN ($in) ORDER BY ge.name", $ids) as $row) {
            $genres[$row['game_id']][] = ['id' => (int) $row['id'], 'name' => $row['name'], 'slug' => $row['slug']];
        }
        $platforms = [];
        foreach ($this->all("SELECT gp.game_id, p.id, p.name, p.slug, p.short_name FROM game_platform gp JOIN platforms p ON p.id = gp.platform_id WHERE gp.game_id IN ($in) ORDER BY gp.position, p.sort_order", $ids) as $row) {
            $platforms[$row['game_id']][] = ['id' => (int) $row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'short_name' => $row['short_name']];
        }

        foreach ($games as &$g) {
            $g['id'] = (int) $g['id'];
            $g['release_year'] = (int) $g['release_year'];
            $g['avg_rating'] = $g['avg_rating'] !== null ? round((float) $g['avg_rating'], 1) : null;
            $g['review_count'] = (int) $g['review_count'];
            $g['fav_count'] = (int) $g['fav_count'];
            $g['genres'] = $genres[$g['id']] ?? [];
            $g['platforms'] = $platforms[$g['id']] ?? [];
        }
        return $games;
    }

    public function genres(): array
    {
        return $this->all('SELECT ge.id, ge.name, ge.slug, COUNT(gg.game_id) AS n FROM genres ge LEFT JOIN game_genre gg ON gg.genre_id = ge.id GROUP BY ge.id, ge.name, ge.slug ORDER BY ge.name');
    }

    public function platforms(): array
    {
        return $this->all('SELECT p.id, p.name, p.slug, p.short_name, COUNT(gp.game_id) AS n FROM platforms p LEFT JOIN game_platform gp ON gp.platform_id = p.id GROUP BY p.id, p.name, p.slug, p.short_name, p.sort_order ORDER BY p.sort_order, p.name');
    }

    /** @return list<int> décennies présentes dans le catalogue */
    public function decades(): array
    {
        $years = array_map('intval', $this->db->query('SELECT DISTINCT release_year FROM games')->fetchAll(\PDO::FETCH_COLUMN));
        $decades = array_values(array_unique(array_map(static fn (int $y) => intdiv($y, 10) * 10, $years)));
        sort($decades);
        return $decades;
    }

    public function count(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM games');
    }

    public function titleExists(string $title, int $exceptId = 0): bool
    {
        return (bool) $this->scalar('SELECT 1 FROM games WHERE LOWER(title) = ? AND id <> ?', [mb_strtolower($title), $exceptId]);
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $this->run(
                'INSERT INTO games (title, developer, release_year, synopsis, created_at) VALUES (?, ?, ?, ?, ?)',
                [$data['title'], $data['developer'], $data['release_year'], $data['synopsis'], $this->now()]
            );
            $id = (int) $this->db->lastInsertId();
            $this->syncTaxonomies($id, $data['genres'], $data['platforms']);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): void
    {
        $this->db->beginTransaction();
        try {
            $this->run(
                'UPDATE games SET title = ?, developer = ?, release_year = ?, synopsis = ?, updated_at = ? WHERE id = ?',
                [$data['title'], $data['developer'], $data['release_year'], $data['synopsis'], $this->now(), $id]
            );
            $this->syncTaxonomies($id, $data['genres'], $data['platforms']);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM games WHERE id = ?', [$id]);
    }

    /** @param list<int> $platformIds dans l'ordre (le premier = plateforme principale) */
    private function syncTaxonomies(int $id, array $genreIds, array $platformIds): void
    {
        $this->run('DELETE FROM game_genre WHERE game_id = ?', [$id]);
        $this->run('DELETE FROM game_platform WHERE game_id = ?', [$id]);
        $g = $this->db->prepare('INSERT INTO game_genre (game_id, genre_id) VALUES (?, ?)');
        foreach ($genreIds as $genreId) {
            $g->execute([$id, $genreId]);
        }
        $p = $this->db->prepare('INSERT INTO game_platform (game_id, platform_id, position) VALUES (?, ?, ?)');
        foreach (array_values($platformIds) as $pos => $platformId) {
            $p->execute([$id, $platformId, $pos]);
        }
    }

    /** @param list<int> $ids */
    public function existingIds(string $table, array $ids): array
    {
        if (!$ids || !in_array($table, ['genres', 'platforms'], true)) {
            return [];
        }
        $rows = $this->all('SELECT id FROM ' . $table . ' WHERE id IN (' . Database::placeholders(count($ids)) . ')', $ids);
        return array_map('intval', array_column($rows, 'id'));
    }
}
