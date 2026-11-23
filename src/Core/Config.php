<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configuration lue depuis les variables d'environnement (et un éventuel fichier .env).
 * SQLite par défaut ; MySQL/MariaDB via DB_DRIVER=mysql + DB_HOST, DB_NAME, etc.
 */
final class Config
{
    private static array $values = [];

    public static function load(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                if (getenv($key) === false) {
                    putenv($key . '=' . trim($value, "\"'"));
                }
            }
        }

        $env = static function (string $key, string $default): string {
            $value = getenv($key);
            return ($value === false || $value === '') ? $default : $value;
        };

        $dbPath = $env('DB_PATH', BASE_PATH . '/var/gamehub.sqlite');
        if (!preg_match('#^([a-zA-Z]:)?[/\\\\]#', $dbPath)) {
            $dbPath = BASE_PATH . '/' . $dbPath; // chemin relatif : depuis la racine du projet
        }

        self::$values = [
            'env'         => $env('APP_ENV', 'production'),
            'demo'        => filter_var($env('APP_DEMO', '1'), FILTER_VALIDATE_BOOLEAN),
            'url'         => rtrim($env('APP_URL', ''), '/'),
            'trust_proxy' => filter_var($env('TRUST_PROXY', '0'), FILTER_VALIDATE_BOOLEAN),
            'db' => [
                'driver' => $env('DB_DRIVER', 'sqlite'),
                'path'   => $dbPath,
                'host'   => $env('DB_HOST', '127.0.0.1'),
                'port'   => $env('DB_PORT', '3306'),
                'name'   => $env('DB_NAME', 'gamehub'),
                'user'   => $env('DB_USER', 'root'),
                'pass'   => $env('DB_PASS', ''),
            ],
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
