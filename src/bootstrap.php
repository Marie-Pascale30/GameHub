<?php

declare(strict_types=1);

/*
 * Amorçage commun au front controller et aux scripts CLI (migrations, seed).
 * Pas de Composer : un autoloader PSR-4 minimal suffit pour l'espace de noms App\.
 */

define('BASE_PATH', dirname(__DIR__));

date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/src/Support/helpers.php';

App\Core\Config::load();
