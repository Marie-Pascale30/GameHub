<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\PasswordReset;
use App\Models\User;
use App\Support\Validator;

final class ProfileController extends Controller
{
    public function show(array $errors = [], array $old = [], int $status = 200): void
    {
        $user = $this->requireAuth();
        $users = new User();

        $this->render('profile/show', [
            'title'      => 'Mon profil',
            'user'       => $user,
            'stats'      => $users->stats($user['id']),
            'genres'     => $users->favoriteGenres($user['id']),
            'activity'   => $users->recentActivity($user['id']),
            'errors'     => $errors,
            'old'        => $old,
        ], $status);
    }

    /** Les comptes de démo sont partagés : leur identité et leur mot de passe ne changent pas. */
    private function refuseForDemo(array $user): void
    {
        if (Auth::isDemo($user)) {
            Session::flash('info', 'Ce compte de démonstration est partagé par tous les visiteurs : son pseudo et son mot de passe ne peuvent pas être modifiés. Créez votre propre compte pour tester cette fonction.');
            Response::redirect('/profil');
        }
    }

    public function updateUsername(): void
    {
        $user = $this->requireAuth();
        $this->refuseForDemo($user);
        $username = Request::string('username', 60);
        $users = new User();

        $error = Validator::username($username);
        if ($error === null && $users->usernameTaken($username, $user['id'])) {
            $error = 'Ce pseudo est déjà pris.';
        }
        if ($error !== null) {
            $this->show(['username' => $error], ['username' => $username], 422);
            return;
        }

        $users->updateUsername($user['id'], $username);
        Session::flash('success', 'Pseudo mis à jour : vous êtes désormais « ' . $username . ' ».');
        Response::redirect('/profil');
    }

    public function updatePassword(): void
    {
        $user = $this->requireAuth();
        $this->refuseForDemo($user);
        $current = Request::password('current_password');
        $new = Request::password('new_password');
        $confirm = Request::password('new_password_confirm');
        $users = new User();

        $errors = [];
        if (!password_verify($current, (string) $users->passwordHash($user['id']))) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        } elseif ($missing = Validator::passwordErrors($new)) {
            $errors['new_password'] = Validator::passwordMessage($missing);
        } elseif (!hash_equals($new, $confirm)) {
            $errors['new_password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        } elseif (hash_equals($current, $new)) {
            $errors['new_password'] = 'Le nouveau mot de passe doit être différent de l’actuel.';
        }
        if ($errors) {
            $this->show($errors, [], 422);
            return;
        }

        $users->updatePassword($user['id'], $new);
        (new PasswordReset())->invalidateForUser($user['id']);
        Auth::login($users->find($user['id'])); // nouvel identifiant de session
        Session::flash('success', 'Mot de passe modifié avec succès.');
        Response::redirect('/profil');
    }

    /** Suppression de son propre compte (droit à l'effacement, RGPD art. 17). */
    public function destroy(): void
    {
        $user = $this->requireAuth();
        if (Auth::isDemo($user)) {
            Session::flash('info', 'Ce compte de démonstration est partagé par tous les visiteurs : il ne peut pas être supprimé.');
            Response::redirect('/profil');
        }

        $users = new User();
        if (!password_verify(Request::password('delete_password'), (string) $users->passwordHash($user['id']))) {
            $this->show(['delete_password' => 'Mot de passe incorrect : votre compte n’a pas été supprimé.'], [], 422);
            return;
        }

        // ON DELETE CASCADE : coups de cœur, notes, avis et jetons de réinitialisation.
        $users->delete($user['id']);
        (new RateLimiter())->clear('login:email:' . $user['email']);

        Auth::logout();
        Session::start();
        Session::flash('success', 'Votre compte et toutes les données associées (coups de cœur, notes, avis) ont été supprimés.');
        Response::redirect('/');
    }
}
