<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Favorite;
use App\Models\Game;

final class FavoriteController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $favorites = new Favorite();
        $ids = $favorites->gameIdsForUser($user['id']);

        $games = [];
        $model = new Game();
        foreach ($ids as $id) {
            if ($game = $model->find($id)) {
                $games[] = $game;
            }
        }

        $this->render('favorites/index', [
            'title'     => 'Mes coups de cœur',
            'games'     => $games,
            'favorites' => array_fill_keys($ids, true),
        ]);
    }

    public function toggle(int $id): void
    {
        $user = $this->requireAuth();
        $game = (new Game())->find($id);
        if ($game === null) {
            Response::error(404, 'Jeu introuvable.');
        }

        $favorites = new Favorite();
        $state = $favorites->toggle($user['id'], $id);
        $message = $state ? '« ' . $game['title'] . ' » ajouté à vos coups de cœur' : '« ' . $game['title'] . ' » retiré de vos coups de cœur';

        if (!Request::isFetch()) {
            Session::flash('success', $message);
            $this->back('/jeu/' . $id);
        }
        Response::json(['ok' => true, 'favorite' => $state, 'count' => $favorites->countForGame($id), 'message' => $message]);
    }
}
