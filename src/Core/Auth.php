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
                // Compte supprimé, ou mot de passe changé depuis une autre session : session révoquée.
                if (self::$user === null
                    || (int) self::$user['session_version'] !== ($_SESSION['session_version'] ?? -1)) {
                    self::$user = null;
                    unset($_SESSION['user_id'], $_SESSION['session_version']);
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

    /** Compte de démonstration partagé publiquement (joueur@ / admin@gamehub.fr). */
    public static function isDemo(?array $user = null): bool
    {
        $user ??= self::user();
        return $user !== null && self::isDemoEmail((string) $user['email']);
    }

    public static function isDemoEmail(string $email): bool
    {
        return in_array(mb_strtolower($email), Config::demoEmails(), true);
    }

    /** Connexion : nouvel identifiant de session + nouveau jeton CSRF. */
    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['session_version'] = (int) ($user['session_version'] ?? 0);
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
