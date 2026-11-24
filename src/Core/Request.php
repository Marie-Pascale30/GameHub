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

    /**
     * IP du client, utilisée pour la limitation des tentatives (même logique que GoStage).
     *
     * Sans proxy (TRUST_PROXY=0, défaut) : REMOTE_ADDR uniquement.
     *
     * Derrière Render (TRUST_PROXY=1) : REMOTE_ADDR est l'adresse d'un proxy local, identique
     * pour tous les visiteurs. Chaque proxy AJOUTE à droite de X-Forwarded-For l'adresse de son
     * interlocuteur ; tout ce qui est à gauche peut avoir été inventé par le client. Relevé réel
     * sur Render (nov. 2026), avec un client qui envoie lui-même « X-Forwarded-For: 1.2.3.4 » :
     *
     *     X-Forwarded-For: 1.2.3.4, <IP du client>, <IP Cloudflare>, <IP interne Render 10.x>
     *
     * Trois proxys de confiance : l'IP du client est la 3e entrée en partant de la droite
     * (TRUSTED_PROXY_HOPS=3). L'ancienne version prenait la dernière entrée, qui est une IP
     * interne de Render partagée par tous les visiteurs : la limite par IP était globale.
     */
    public static function ip(): string
    {
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (!Config::get('trust_proxy')) {
            return $remote;
        }

        $hops = max(1, (int) Config::get('trusted_proxy_hops'));
        $parts = array_map('trim', explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')));
        if (count($parts) < $hops) {
            return $remote; // la requête n'est pas passée par toute la chaîne de proxys attendue
        }
        $ip = $parts[count($parts) - $hops];
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $remote;
    }

    /**
     * URL publique, toujours issue de la configuration (APP_URL) : l'en-tête Host est fourni
     * par le client et n'est jamais utilisé pour construire un lien ou valider un Referer.
     */
    public static function baseUrl(): string
    {
        return (string) Config::get('url');
    }
}
