<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Jetons de réinitialisation : seul le SHA-256 du jeton est stocké,
 * usage unique, expiration courte, anciens jetons invalidés.
 */
final class PasswordReset extends Model
{
    public const TTL = 1800; // 30 minutes

    public function issue(int $userId): string
    {
        $this->invalidateForUser($userId);
        $token = bin2hex(random_bytes(32));
        $this->run(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)',
            [$userId, hash('sha256', $token), time() + self::TTL, time()]
        );
        return $token;
    }

    public function findValid(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return $this->one(
            'SELECT id, user_id, expires_at FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > ?',
            [hash('sha256', $token), time()]
        );
    }

    public function markUsed(int $id): void
    {
        $this->run('UPDATE password_resets SET used_at = ? WHERE id = ?', [time(), $id]);
    }

    public function invalidateForUser(int $userId): void
    {
        $this->run('UPDATE password_resets SET used_at = ? WHERE user_id = ? AND used_at IS NULL', [time(), $userId]);
    }
}
