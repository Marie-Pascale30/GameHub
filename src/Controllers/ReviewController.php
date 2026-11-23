<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Game;
use App\Models\Review;

final class ReviewController extends Controller
{
    public function save(int $id): void
    {
        $user = $this->requireAuth();
        $game = $this->gameOr404($id);

        $rating = Request::int('rating');
        $body = Request::string('body', 2000);

        if ($rating < 1 || $rating > 5) {
            $this->fail($id, 'Choisissez une note entre 1 et 5 étoiles.');
        }
        if (mb_strlen($body) > Review::BODY_MAX) {
            $this->fail($id, 'Votre avis dépasse ' . Review::BODY_MAX . ' caractères.');
        }

        $created = (new Review())->save($user['id'], $id, $rating, $body !== '' ? $body : null);
        $this->respond($id, $created ? 'Merci ! Votre avis sur « ' . $game['title'] . ' » est publié.' : 'Votre avis a été mis à jour.');
    }

    public function delete(int $id): void
    {
        $user = $this->requireAuth();
        $this->gameOr404($id);
        (new Review())->delete($user['id'], $id);
        $this->respond($id, 'Votre avis a été supprimé.');
    }

    private function gameOr404(int $id): array
    {
        $game = (new Game())->find($id);
        if ($game === null) {
            Response::error(404, 'Jeu introuvable.');
        }
        return $game;
    }

    private function fail(int $id, string $message): never
    {
        if (Request::isFetch()) {
            Response::json(['ok' => false, 'error' => $message], 422);
        }
        Session::flash('error', $message);
        Response::redirect('/jeu/' . $id . '#avis');
    }

    /** Renvoie le bloc « avis » re-rendu côté serveur pour remplacer celui de la page. */
    private function respond(int $gameId, string $message): never
    {
        if (!Request::isFetch()) {
            Session::flash('success', $message);
            Response::redirect('/jeu/' . $gameId . '#avis');
        }

        $reviews = new Review();
        $summary = $reviews->summary($gameId);
        $user = \App\Core\Auth::user();

        Response::json([
            'ok'      => true,
            'message' => $message,
            'average' => $summary['average'],
            'count'   => $summary['count'],
            'scoreHtml' => View::fetch('game/_score', ['summary' => $summary]),
            'html'    => View::fetch('game/_reviews', [
                'game'     => (new Game())->find($gameId),
                'summary'  => $summary,
                'reviews'  => $reviews->forGame($gameId),
                'myReview' => $reviews->findForUser($user['id'], $gameId),
            ]),
        ]);
    }
}
