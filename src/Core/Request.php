<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private static ?array $json = null;

    public static function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        return $method === 'HEAD' ? 'GET' : $method;
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';
        $path = '/' . trim($path, '/');
        return $path;
    }

    /** Corps JSON (fetch) décodé, sinon tableau vide. */
    public static function json(): array
    {
        if (self::$json === null) {
            self::$json = [];
            if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
                $data = json_decode((string) file_get_contents('php://input'), true);
                if (is_array($data)) {
                    self::$json = $data;
                }
            }
        }
        return self::$json;
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        return self::json()[$key] ?? $default;
    }

    /** Valeur scalaire nettoyée (trim) ; les tableaux injectés sont ignorés. */
    public static function string(string $key, int $maxLength = 5000): string
    {
        $value = self::input($key, '');
        if (!is_string($value) && !is_int($value)) {
            return '';
        }
        return mb_substr(trim((string) $value), 0, $maxLength);
    }

    /** Mot de passe : jamais tronqué ni nettoyé (les espaces comptent). */
    public static function password(string $key): string
    {
        $value = self::input($key, '');
        return is_string($value) ? mb_substr($value, 0, 200) : '';
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::input($key, $default);
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : $default;
    }

    /** @return list<int> */
    public static function intList(string $key): array
    {
        $value = self::input($key, []);
        if (!is_array($value)) {
            return [];
        }
        $ids = array_filter(array_map(static fn ($v) => filter_var($v, FILTER_VALIDATE_INT), $value), static fn ($v) => $v !== false && $v > 0);
        return array_values(array_unique($ids));
    }

    public static function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? mb_substr(trim($value), 0, 100) : $default;
    }

    public static function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_starts_with(self::path(), '/api/');
    }

    /** Requête fetch() de notre JS (Accept: application/json). */
    public static function isFetch(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        return Config::get('trust_proxy') && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    public static function ip(): string
    {
        if (Config::get('trust_proxy') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Derrière le proxy de l'hébergeur : la dernière entrée est celle ajoutée par le proxy.
            $parts = array_map('trim', explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']));
            $ip = end($parts);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function baseUrl(): string
    {
        $configured = Config::get('url');
        if ($configured !== '') {
            return $configured;
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!preg_match('/^[a-z0-9.\-]+(:\d+)?$/i', $host)) {
            $host = 'localhost';
        }
        return (self::isHttps() ? 'https' : 'http') . '://' . $host;
    }
}
