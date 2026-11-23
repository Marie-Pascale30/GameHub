<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Jeton CSRF de session (synchronizer token).
 * Formulaires : champ caché _csrf. Requêtes fetch : en-tête X-CSRF-Token.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function rotate(): void
    {
        unset($_SESSION['_csrf']);
    }

    public static function isValid(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['_csrf'])
            && is_string($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    public static function fromRequest(): mixed
    {
        return $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? null;
    }
}
