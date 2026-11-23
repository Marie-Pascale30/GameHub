<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Favorite;
use App\Models\Game;
use App\Models\Review;

final class GameController extends Controller
{
    public function show(int $id): void
    {
        $games = new Game();
        $game = $games->find($id);
        if ($game === null) {
            Response::error(404, 'Ce jeu n’existe pas (ou plus) dans le catalogue.');
        }

        $reviews = new Review();
        $userId = Auth::id();

        $this->render('game/show', [
            'title'      => $game['title'],
            'game'       => $game,
            'summary'    => $reviews->summary($id),
            'reviews'    => $reviews->forGame($id),
            'myReview'   => $userId ? $reviews->findForUser($userId, $id) : null,
            'isFavorite' => $userId ? (new Favorite())->isFavorite($userId, $id) : false,
            'similar'    => $games->similar($game),
            'favorites'  => (new Favorite())->idSet($userId),
        ]);
    }
}
