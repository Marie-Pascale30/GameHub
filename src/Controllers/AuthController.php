<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Support\Validator;

final class AuthController extends Controller
{
    private const MAX_PER_EMAIL = 5;
    private const MAX_PER_IP = 20;
    private const WINDOW = 900; // 15 minutes

    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }
        $this->render('auth/login', ['title' => 'Connexion', 'email' => '', 'next' => Response::safePath(Request::query('next'), '')]);
    }

    public function login(): void
    {
        $email = mb_strtolower(Request::string('email', 190));
        $password = Request::password('password');
        $next = Response::safePath(Request::string('next', 200), '/');
        $limiter = new RateLimiter();
        $emailKey = 'login:email:' . $email;
        $ipKey = 'login:ip:' . Request::ip();
        // Comptes de démo : identifiants publics, donc pas de blocage par e-mail (sinon 5 essais faux
        // suffiraient à les bloquer pour tout le monde). La limite par IP s'applique toujours.
        $limitEmail = !Auth::isDemoEmail($email);

        $wait = max(
            $limitEmail ? $limiter->availableIn($emailKey, self::MAX_PER_EMAIL, self::WINDOW) : 0,
            $limiter->availableIn($ipKey, self::MAX_PER_IP, self::WINDOW)
        );
        if ($wait > 0) {
            header('Retry-After: ' . $wait);
            $this->render('auth/login', [
                'title' => 'Connexion', 'email' => $email, 'next' => $next,
                'error' => 'Trop de tentatives de connexion. Réessayez dans ' . plural((int) ceil($wait / 60), 'minute') . '.',
            ], 429);
            return;
        }

        $users = new User();
        $user = $email !== '' ? $users->findWithHashByEmail($email) : null;
        // Hash factice : temps de réponse identique que le compte existe ou non (anti-énumération).
        $hash = $user['password_hash'] ?? password_hash(random_bytes(12), PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash) && $user !== null;

        if (!$valid) {
            if ($limitEmail) {
                $limiter->hit($emailKey);
            }
            $limiter->hit($ipKey);
            $this->render('auth/login', [
                'title' => 'Connexion', 'email' => $email, 'next' => $next,
                'error' => 'E-mail ou mot de passe incorrect.',
            ], 422);
            return;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $users->rehash((int) $user['id'], $password);
        }
        unset($user['password_hash']);

        $limiter->clear($emailKey);
        Auth::login($user);
        Session::flash('success', 'Bon retour parmi nous, ' . $user['username'] . ' !');
        Response::redirect($next);
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }
        $this->render('auth/register', ['title' => 'Inscription', 'old' => [], 'errors' => []]);
    }

    public function register(): void
    {
        $username = Request::string('username', 60);
        $email = mb_strtolower(Request::string('email', 190));
        $password = Request::password('password');
        $confirm = Request::password('password_confirm');
        $users = new User();
        $errors = [];

        if ($msg = Validator::username($username)) {
            $errors['username'] = $msg;
        } elseif ($users->usernameTaken($username)) {
            $errors['username'] = 'Ce pseudo est déjà pris.';
        }
        if ($msg = Validator::email($email)) {
            $errors['email'] = $msg;
        } elseif ($users->emailExists($email)) {
            $errors['email'] = 'Un compte existe déjà avec cette adresse.';
        }
        if ($missing = Validator::passwordErrors($password)) {
            $errors['password'] = Validator::passwordMessage($missing);
        } elseif (!hash_equals($password, $confirm)) {
            $errors['password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if ($errors) {
            $this->render('auth/register', [
                'title' => 'Inscription', 'errors' => $errors,
                'old' => ['username' => $username, 'email' => $email],
            ], 422);
            return;
        }

        $id = $users->create($username, $email, $password);
        Auth::login($users->find($id));
        Session::flash('success', 'Bienvenue sur GameHub, ' . $username . ' ! Ajoutez vos premiers coups de cœur.');
        Response::redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('info', 'Vous êtes déconnecté. À bientôt !');
        Response::redirect('/');
    }
}
