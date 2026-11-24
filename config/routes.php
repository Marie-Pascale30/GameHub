<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CatalogController;
use App\Controllers\FavoriteController;
use App\Controllers\GameController;
use App\Controllers\PageController;
use App\Controllers\PasswordController;
use App\Controllers\ProfileController;
use App\Controllers\ReviewController;
use App\Core\Router;

return static function (Router $r): void {
    // Catalogue
    $r->get('/', [CatalogController::class, 'index']);
    $r->get('/jeu/{id}', [GameController::class, 'show']);
    $r->get('/mentions-legales', [PageController::class, 'legal']);

    // Authentification
    $r->get('/connexion', [AuthController::class, 'showLogin']);
    $r->post('/connexion', [AuthController::class, 'login']);
    $r->get('/inscription', [AuthController::class, 'showRegister']);
    $r->post('/inscription', [AuthController::class, 'register']);
    $r->post('/deconnexion', [AuthController::class, 'logout']);

    // Mot de passe oublié
    $r->get('/mot-de-passe-oublie', [PasswordController::class, 'showForgot']);
    $r->post('/mot-de-passe-oublie', [PasswordController::class, 'sendLink']);
    $r->get('/reinitialisation', [PasswordController::class, 'showReset']);
    $r->post('/reinitialisation', [PasswordController::class, 'reset']);

    // Espace joueur
    $r->get('/favoris', [FavoriteController::class, 'index']);
    $r->get('/profil', [ProfileController::class, 'show']);
    $r->post('/profil/pseudo', [ProfileController::class, 'updateUsername']);
    $r->post('/profil/mot-de-passe', [ProfileController::class, 'updatePassword']);
    $r->post('/compte/supprimer', [ProfileController::class, 'destroy']);

    // API JSON (fetch)
    $r->get('/api/jeux', [CatalogController::class, 'api']);
    $r->post('/api/jeux/{id}/favori', [FavoriteController::class, 'toggle']);
    $r->post('/api/jeux/{id}/avis', [ReviewController::class, 'save']);
    $r->post('/api/jeux/{id}/avis/supprimer', [ReviewController::class, 'delete']);

    // Administration
    $r->get('/admin/jeux', [AdminController::class, 'index']);
    $r->get('/admin/jeux/nouveau', [AdminController::class, 'create']);
    $r->post('/admin/jeux', [AdminController::class, 'store']);
    $r->get('/admin/jeux/{id}/modifier', [AdminController::class, 'edit']);
    $r->post('/admin/jeux/{id}', [AdminController::class, 'update']);
    $r->post('/admin/jeux/{id}/supprimer', [AdminController::class, 'destroy']);
};
