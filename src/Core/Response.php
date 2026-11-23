<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function redirect(string $to, int $status = 303): never
    {
        header('Location: ' . $to, true, $status);
        exit;
    }

    /** Page ou JSON d'erreur selon le type de requête. */
    public static function error(int $status, string $message, ?string $template = null): never
    {
        if (Request::wantsJson()) {
            self::json(['ok' => false, 'error' => $message], $status);
        }
        http_response_code($status);
        $template ??= in_array($status, [404, 429], true) ? 'errors/' . $status : 'errors/generic';
        View::render($template, ['title' => 'Erreur ' . $status, 'status' => $status, 'message' => $message]);
        exit;
    }

    /** N'accepte que des chemins internes (anti open-redirect). */
    public static function safePath(?string $path, string $fallback = '/'): string
    {
        if (!is_string($path) || $path === '' || $path[0] !== '/' || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return $fallback;
        }
        return $path;
    }
}
