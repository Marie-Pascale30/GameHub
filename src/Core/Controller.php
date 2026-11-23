<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        View::render($template, $data);
    }

    protected function requireAuth(): array
    {
        $user = Auth::user();
        if ($user !== null) {
            return $user;
        }
        if (Request::wantsJson()) {
            Response::json(['ok' => false, 'error' => 'Connectez-vous pour continuer.', 'login' => '/connexion'], 401);
        }
        Session::flash('info', 'Connectez-vous pour accéder à cette page.');
        $next = Request::method() === 'GET' ? Request::path() : '/';
        Response::redirect('/connexion?next=' . rawurlencode($next));
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            Response::error(403, 'Accès réservé aux administrateurs.');
        }
        return $user;
    }

    protected function back(string $fallback = '/'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $path = parse_url($referer, PHP_URL_PATH);
        $query = parse_url($referer, PHP_URL_QUERY);
        $host = parse_url($referer, PHP_URL_HOST);
        $sameHost = $host === null || $host === parse_url(Request::baseUrl(), PHP_URL_HOST);
        $target = $sameHost && is_string($path) ? $path . ($query ? '?' . $query : '') : $fallback;
        Response::redirect(Response::safePath($target, $fallback));
    }
}
