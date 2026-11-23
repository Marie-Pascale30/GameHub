<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Connexion PDO unique. Requêtes préparées natives (pas d'émulation),
 * exceptions activées, clés étrangères forcées sous SQLite.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $cfg = Config::get('db');
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($cfg['driver'] === 'mysql') {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['name']);
            $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
        } else {
            $dir = dirname($cfg['path']);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $pdo = new PDO('sqlite:' . $cfg['path'], null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
            $pdo->exec('PRAGMA journal_mode = WAL');
        }

        return self::$pdo = $pdo;
    }

    public static function driver(): string
    {
        return Config::get('db.driver') === 'mysql' ? 'mysql' : 'sqlite';
    }

    /** Génère "?, ?, ?" pour une clause IN (...). */
    public static function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, max(1, $count), '?'));
    }
}
