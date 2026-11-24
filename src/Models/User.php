<?php

declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    private const COLUMNS = 'id, username, email, role, created_at';

    public function find(int $id): ?array
    {
        return $this->one('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?', [$id]);
    }

    /** Inclut le hash : réservé à la vérification du mot de passe. */
    public function findWithHashByEmail(string $email): ?array
    {
        return $this->one('SELECT id, username, email, role, created_at, password_hash FROM users WHERE email = ?', [mb_strtolower($email)]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->one('SELECT ' . self::COLUMNS . ' FROM users WHERE email = ?', [mb_strtolower($email)]);
    }

    public function passwordHash(int $id): ?string
    {
        $hash = $this->scalar('SELECT password_hash FROM users WHERE id = ?', [$id]);
        return is_string($hash) ? $hash : null;
    }

    public function emailExists(string $email): bool
    {
        return (bool) $this->scalar('SELECT 1 FROM users WHERE email = ?', [mb_strtolower($email)]);
    }

    public function usernameTaken(string $username, ?int $exceptId = null): bool
    {
        return (bool) $this->scalar(
            'SELECT 1 FROM users WHERE LOWER(username) = ? AND id <> ?',
            [mb_strtolower($username), $exceptId ?? 0]
        );
    }

    public function create(string $username, string $email, string $password, string $role = 'user'): int
    {
        $this->run(
            'INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)',
            [$username, mb_strtolower($email), password_hash($password, PASSWORD_DEFAULT), $role, $this->now()]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateUsername(int $id, string $username): void
    {
        $this->run('UPDATE users SET username = ?, updated_at = ? WHERE id = ?', [$username, $this->now(), $id]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $this->run(
            'UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $this->now(), $id]
        );
    }

    public function rehash(int $id, string $password): void
    {
        $this->run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    /** Coups de cœur, avis et jetons de réinitialisation suivent (ON DELETE CASCADE). */
    public function delete(int $id): void
    {
        $this->run('DELETE FROM users WHERE id = ?', [$id]);
    }

    public function stats(int $id): array
    {
        $row = $this->one(
            'SELECT
                (SELECT COUNT(*) FROM favorites WHERE user_id = ?) AS favorites,
                (SELECT COUNT(*) FROM reviews WHERE user_id = ?) AS reviews,
                (SELECT AVG(rating) FROM reviews WHERE user_id = ?) AS avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE user_id = ? AND body IS NOT NULL AND body <> \'\') AS written',
            [$id, $id, $id, $id]
        );
        return [
            'favorites'  => (int) $row['favorites'],
            'reviews'    => (int) $row['reviews'],
            'written'    => (int) $row['written'],
            'avg_rating' => $row['avg_rating'] !== null ? round((float) $row['avg_rating'], 1) : null,
        ];
    }

    /** Genres les plus présents parmi les favoris et les jeux notés 4+ . */
    public function favoriteGenres(int $id, int $limit = 5): array
    {
        return $this->all(
            'SELECT ge.name, ge.slug, COUNT(*) AS n
             FROM (
                SELECT game_id FROM favorites WHERE user_id = ?
                UNION
                SELECT game_id FROM reviews WHERE user_id = ? AND rating >= 4
             ) t
             JOIN game_genre gg ON gg.game_id = t.game_id
             JOIN genres ge ON ge.id = gg.genre_id
             GROUP BY ge.id, ge.name, ge.slug
             ORDER BY n DESC, ge.name ASC
             LIMIT ' . max(1, $limit),
            [$id, $id]
        );
    }

    public function recentActivity(int $id, int $limit = 8): array
    {
        return $this->all(
            'SELECT * FROM (
                SELECT \'favorite\' AS kind, f.created_at AS happened_at, g.id AS game_id, g.title, NULL AS rating
                FROM favorites f JOIN games g ON g.id = f.game_id WHERE f.user_id = ?
                UNION ALL
                SELECT \'review\' AS kind, COALESCE(r.updated_at, r.created_at) AS happened_at, g.id AS game_id, g.title, r.rating
                FROM reviews r JOIN games g ON g.id = r.game_id WHERE r.user_id = ?
             ) activity
             ORDER BY happened_at DESC
             LIMIT ' . max(1, $limit),
            [$id, $id]
        );
    }

    public function count(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM users');
    }
}
