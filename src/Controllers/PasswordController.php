<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\PasswordReset;
use App\Models\User;
use App\Support\Validator;

final class PasswordController extends Controller
{
    public function showForgot(): void
    {
        $this->render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => '']);
    }

    public function sendLink(): void
    {
        $email = mb_strtolower(Request::string('email', 190));
        $limiter = new RateLimiter();
        $key = 'reset:ip:' . Request::ip();

        if (($wait = $limiter->availableIn($key, 5, 900)) > 0) {
            header('Retry-After: ' . $wait);
            $this->render('auth/forgot', [
                'title' => 'Mot de passe oublié', 'email' => $email,
                'error' => 'Trop de demandes. Réessayez dans ' . plural((int) ceil($wait / 60), 'minute') . '.',
            ], 429);
            return;
        }
        $limiter->hit($key);

        if (Validator::email($email) !== null) {
            $this->render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => $email, 'error' => 'Adresse e-mail invalide.'], 422);
            return;
        }

        // SÉCURITÉ : aucun lien n'est affiché ni journalisé. L'ancien mode démo affichait le lien pour
        // n'importe quelle adresse, ce qui permettait de prendre le contrôle de n'importe quel compte.
        // Ce déploiement n'a pas de serveur SMTP : aucun jeton n'est donc émis (il ne pourrait pas être remis).
        // Pour activer la fonction, brancher un envoi d'e-mail ici puis appeler PasswordReset::issue().

        $this->render('auth/forgot', [
            'title'    => 'Mot de passe oublié',
            'email'    => '',
            'sent'     => true,
        ]);
    }

    public function showReset(): void
    {
        $token = Request::query('token', '');
        $valid = (new PasswordReset())->findValid($token) !== null;
        $this->render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token, 'invalid' => !$valid], $valid ? 200 : 410);
    }

    public function reset(): void
    {
        $token = Request::string('token', 64);
        $password = Request::password('password');
        $confirm = Request::password('password_confirm');
        $resets = new PasswordReset();
        $reset = $resets->findValid($token);

        // Les comptes de démo partagés ne changent jamais de mot de passe, même avec un jeton valide.
        $owner = $reset !== null ? (new User())->find((int) $reset['user_id']) : null;
        if ($reset === null || $owner === null || Auth::isDemo($owner)) {
            $this->render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => '', 'invalid' => true], 410);
            return;
        }

        $error = null;
        if ($missing = Validator::passwordErrors($password)) {
            $error = Validator::passwordMessage($missing);
        } elseif (!hash_equals($password, $confirm)) {
            $error = 'Les deux mots de passe ne correspondent pas.';
        }
        if ($error) {
            $this->render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token, 'invalid' => false, 'error' => $error], 422);
            return;
        }

        $users = new User();
        $userId = (int) $reset['user_id'];
        $users->updatePassword($userId, $password);
        $resets->markUsed((int) $reset['id']);
        $resets->invalidateForUser($userId);
        if ($user = $users->find($userId)) {
            (new RateLimiter())->clear('login:email:' . $user['email']);
        }

        Session::flash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
        Response::redirect('/connexion');
    }
}
