<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Favorite;
use App\Models\Game;
use App\Models\Review;
use App\Models\User;

final class CatalogController extends Controller
{
    public function index(): void
    {
        $games = new Game();
        $filters = $this->filters($games);
        $result = $games->search($filters, max(1, (int) Request::query('page', '1')));

        $this->render('catalog/index', [
            'title'     => 'Catalogue',
            'filters'   => $filters,
            'result'    => $result,
            'favorites' => (new Favorite())->idSet(Auth::id()),
            'genres'    => $games->genres(),
            'platforms' => $games->platforms(),
            'decades'   => $games->decades(),
            'sorts'     => Game::SORTS,
            'stats'     => [
                'games'   => $games->count(),
                'reviews' => (new Review())->count(),
                'players' => (new User())->count(),
            ],
        ]);
    }

    /** GET /api/jeux : même recherche, réponse JSON (cartes pré-rendues + données). */
    public function api(): void
    {
        $games = new Game();
        $filters = $this->filters($games);
        $result = $games->search($filters, max(1, (int) Request::query('page', '1')));
        $favorites = (new Favorite())->idSet(Auth::id());

        Response::json([
            'ok'      => true,
            'total'   => $result['total'],
            'page'    => $result['page'],
            'pages'   => $result['pages'],
            'hasMore' => $result['page'] < $result['pages'],
            'html'    => View::fetch('partials/game-grid', ['games' => $result['items'], 'favorites' => $favorites]),
            'items'   => array_map(static fn (array $g) => [
                'id'        => $g['id'],
                'title'     => $g['title'],
                'year'      => $g['release_year'],
                'developer' => $g['developer'],
                'rating'    => $g['avg_rating'],
                'genres'    => array_column($g['genres'], 'name'),
                'platforms' => array_column($g['platforms'], 'name'),
                'favorite'  => isset($favorites[$g['id']]),
            ], $result['items']),
        ]);
    }

    /** Filtres validés contre les valeurs réellement présentes en base. */
    private function filters(Game $games): array
    {
        $genre = Request::query('genre');
        $platform = Request::query('plateforme');
        $decade = (int) Request::query('decennie');
        $sort = Request::query('tri', 'populaire');

        return [
            'q'        => mb_substr(Request::query('q'), 0, 60),
            'genre'    => in_array($genre, array_column($games->genres(), 'slug'), true) ? $genre : '',
            'platform' => in_array($platform, array_column($games->platforms(), 'slug'), true) ? $platform : '',
            'decade'   => in_array($decade, $games->decades(), true) ? $decade : 0,
            'sort'     => array_key_exists($sort, Game::SORTS) ? $sort : 'populaire',
        ];
    }
}
