<?php

declare(strict_types=1);

namespace App\Models;

final class Favorite extends Model
{
    /** @return bool nouvel état (true = ajouté aux favoris) */
    public function toggle(int $userId, int $gameId): bool
    {
        if ($this->isFavorite($userId, $gameId)) {
            $this->run('DELETE FROM favorites WHERE user_id = ? AND game_id = ?', [$userId, $gameId]);
            return false;
        }
        $this->run('INSERT INTO favorites (user_id, game_id, created_at) VALUES (?, ?, ?)', [$userId, $gameId, $this->now()]);
        return true;
    }

    public function isFavorite(int $userId, int $gameId): bool
    {
        return (bool) $this->scalar('SELECT 1 FROM favorites WHERE user_id = ? AND game_id = ?', [$userId, $gameId]);
    }

    /** @return array<int,true> ensemble des jeux favoris (accès O(1) dans les templates) */
    public function idSet(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }
        $ids = array_column($this->all('SELECT game_id FROM favorites WHERE user_id = ?', [$userId]), 'game_id');
        return array_fill_keys(array_map('intval', $ids), true);
    }

    public function gameIdsForUser(int $userId): array
    {
        return array_map('intval', array_column(
            $this->all('SELECT game_id FROM favorites WHERE user_id = ? ORDER BY created_at DESC', [$userId]),
            'game_id'
        ));
    }

    public function countForGame(int $gameId): int
    {
        return (int) $this->scalar('SELECT COUNT(*) FROM favorites WHERE game_id = ?', [$gameId]);
    }
}
