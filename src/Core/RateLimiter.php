<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Limiteur à fenêtre glissante stocké en base (fonctionne sans Redis/APCu).
 * Clés utilisées : "login:email:<email>", "login:ip:<ip>", "reset:ip:<ip>".
 */
final class RateLimiter
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    /** Secondes à attendre avant un nouvel essai (0 = autorisé). */
    public function availableIn(string $key, int $maxAttempts, int $windowSeconds): int
    {
        $since = time() - $windowSeconds;
        $stmt = $this->db->prepare('SELECT COUNT(*) AS n, MIN(hit_at) AS oldest FROM rate_limits WHERE rate_key = ? AND hit_at > ?');
        $stmt->execute([$this->hashKey($key), $since]);
        $row = $stmt->fetch();
        if ((int) $row['n'] < $maxAttempts) {
            return 0;
        }
        return max(1, (int) $row['oldest'] + $windowSeconds - time());
    }

    public function hit(string $key): void
    {
        $this->db->prepare('INSERT INTO rate_limits (rate_key, hit_at) VALUES (?, ?)')
            ->execute([$this->hashKey($key), time()]);

        // Ménage opportuniste des entrées de plus de 24 h.
        if (random_int(1, 50) === 1) {
            $this->db->prepare('DELETE FROM rate_limits WHERE hit_at < ?')->execute([time() - 86400]);
        }
    }

    public function clear(string $key): void
    {
        $this->db->prepare('DELETE FROM rate_limits WHERE rate_key = ?')->execute([$this->hashKey($key)]);
    }

    /** Les e-mails / IP ne sont pas stockés en clair. */
    private function hashKey(string $key): string
    {
        return hash('sha256', mb_strtolower($key));
    }
}
