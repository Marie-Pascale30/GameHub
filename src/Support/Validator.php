<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    public const PASSWORD_MIN = 10;

    /** Règles de robustesse héritées de la v1, mais enfin toutes appliquées (ET logique). */
    public static function passwordErrors(string $password): array
    {
        $errors = [];
        if (mb_strlen($password) < self::PASSWORD_MIN) {
            $errors[] = 'au moins ' . self::PASSWORD_MIN . ' caractères';
        }
        if (!preg_match('/\p{Lu}/u', $password)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/\p{Ll}/u', $password)) {
            $errors[] = 'une minuscule';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^\p{L}\d]/u', $password)) {
            $errors[] = 'un caractère spécial';
        }
        return $errors;
    }

    public static function passwordMessage(array $missing): string
    {
        return 'Mot de passe trop faible : il manque ' . implode(', ', $missing) . '.';
    }

    public static function username(string $username): ?string
    {
        if (!preg_match('/^[\p{L}0-9_.\-]{3,30}$/u', $username)) {
            return 'Le pseudo doit contenir 3 à 30 caractères (lettres, chiffres, « _ », « . » ou « - »).';
        }
        return null;
    }

    public static function email(string $email): ?string
    {
        if (mb_strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'Adresse e-mail invalide.';
        }
        return null;
    }
}
