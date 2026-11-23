<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cycle de vie d'une requête HTTP : en-têtes de sécurité, session,
 * vérification CSRF de toute requête POST, routage, gestion des erreurs.
 */
final class App
{
    public static function run(): void
    {
        self::registerErrorHandler();
        self::sendSecurityHeaders();
        Session::start();

        $method = Request::method();
        if ($method === 'POST' && !Csrf::isValid(Csrf::fromRequest())) {
            // 403 (Apache ne connaît pas le code 419) avec une page dédiée « session expirée ».
            Response::error(403, 'Jeton de sécurité (CSRF) invalide ou expiré. Rechargez la page puis réessayez.', 'errors/csrf');
        }

        $router = new Router();
        (require BASE_PATH . '/config/routes.php')($router);
        $router->dispatch($method, Request::path());
    }

    private static function sendSecurityHeaders(): void
    {
        header_remove('X-Powered-By');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        if (Request::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }

    private static function registerErrorHandler(): void
    {
        $debug = Config::get('env') === 'dev';
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (\Throwable $e) use ($debug): void {
            error_log('[GameHub] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $message = $debug ? $e->getMessage() : 'Une erreur inattendue est survenue. Réessayez dans un instant.';
            try {
                Response::error(500, $message);
            } catch (\Throwable) {
                http_response_code(500);
                echo 'Erreur interne.';
            }
        });
    }
}
