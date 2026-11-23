<?php

declare(strict_types=1);

/*
 * Front controller : point d'entrée unique de l'application.
 * Seul le dossier public/ est exposé par le serveur web.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

App\Core\App::run();
