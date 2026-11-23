<?php

declare(strict_types=1);

namespace App\Controllers;

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

        $demoLink = null;
        $user = (new User())->findByEmail($email);
        if ($user) {
            $token = (new PasswordReset())->issue((int) $user['id']);
            $link = Request::baseUrl() . '/reinitialisation?token=' . $token;
            // Pas de serveur SMTP : le lien est journalisé (logs du conteneur) et affiché en mode démo.
            error_log('[GameHub][DÉMO] Lien de réinitialisation pour ' . $email . ' : ' . $link);
            if (Config::get('demo')) {
                $demoLink = $link;
            }
        }

        $this->render('auth/forgot', [
            'title'    => 'Mot de passe oublié',
            'email'    => '',
            'sent'     => true,
            'demoLink' => $demoLink,
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

        if ($reset === null) {
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
