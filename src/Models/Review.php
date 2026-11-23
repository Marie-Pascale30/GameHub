<?php

declare(strict_types=1);

namespace App\Models;

final class Review extends Model
{
    public const BODY_MAX = 600;

    public function forGame(int $gameId): array
    {
        return $this->all(
            'SELECT r.id, r.user_id, r.rating, r.body, r.created_at, r.updated_at, u.username
             FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.game_id = ?
             ORDER BY COALESCE(r.updated_at, r.created_at) DESC',
            [$gameId]
        );
    }

    public function findForUser(int $userId, int $gameId): ?array
    {
        return $this->one('SELECT id, rating, body, created_at, updated_at FROM reviews WHERE user_id = ? AND game_id = ?', [$userId, $gameId]);
    }

    /** Une seule note par joueur et par jeu : création ou mise à jour. */
    public function save(int $userId, int $gameId, int $rating, ?string $body): bool
    {
        $existing = $this->findForUser($userId, $gameId);
        if ($existing) {
            $this->run('UPDATE reviews SET rating = ?, body = ?, updated_at = ? WHERE id = ?', [$rating, $body, $this->now(), $existing['id']]);
            return false;
        }
        $this->run(
            'INSERT INTO reviews (user_id, game_id, rating, body, created_at) VALUES (?, ?, ?, ?, ?)',
            [$userId, $gameId, $rating, $body, $this->now()]
        );
        return true;
    }

    public function delete(int $userId, int $gameId): bool
    {
        return $this->run('DELETE FROM reviews WHERE user_id = ? AND game_id = ?', [$userId, $gameId]) > 0;
    }

    /** Moyenne, nombre d'avis et répartition 5 → 1 étoiles. */
    public function summary(int $gameId): array
    {
        $rows = $this->all('SELECT rating, COUNT(*) AS n FROM reviews WHERE game_id = ? GROUP BY rating', [$gameId]);
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $count = 0;
        $sum = 0;
        foreach ($rows as $row) {
            $distribution[(int) $row['rating']] = (int) $row['n'];
            $count += (int) $row['n'];
            $sum += (int) $row['rating'] * (int) $row['n'];
        }
        return [
            'average'      => $count ? round($sum / $count, 1) : null,
            'count'        => $count,
            'distribution' => $distribution,
        ];
    }

    public function count(): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM reviews');
    }
}
