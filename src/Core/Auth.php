<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    public static function user(): ?array
    {
        if (!self::$resolved) {
            self::$resolved = true;
            $id = $_SESSION['user_id'] ?? null;
            if (is_int($id)) {
                self::$user = (new User())->find($id);
                if (self::$user === null) {
                    unset($_SESSION['user_id']);
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    /** Connexion : nouvel identifiant de session + nouveau jeton CSRF. */
    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        $_SESSION['user_id'] = (int) $user['id'];
        self::$user = $user;
        self::$resolved = true;
    }

    public static function refresh(): void
    {
        self::$resolved = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$resolved = true;
    }
}
