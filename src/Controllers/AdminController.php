<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Game;

final class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $games = new Game();
        $result = $games->search(['sort' => 'titre', 'q' => Request::query('q')], 1, 500);

        $this->render('admin/index', [
            'title' => 'Administration — jeux',
            'games' => $result['items'],
            'q'     => Request::query('q'),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->form(null, ['title' => '', 'developer' => '', 'release_year' => '', 'synopsis' => '', 'genres' => [], 'platforms' => []]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $games = new Game();
        [$data, $errors] = $this->validate($games);
        if ($errors) {
            $this->form(null, $data, $errors, 422);
            return;
        }
        $id = $games->create($data);
        Session::flash('success', '« ' . $data['title'] . ' » a été ajouté au catalogue.');
        Response::redirect('/jeu/' . $id);
    }

    public function edit(int $id): void
    {
        $this->requireAdmin();
        $game = $this->findOr404($id);
        $this->form($game, [
            'title'        => $game['title'],
            'developer'    => $game['developer'],
            'release_year' => $game['release_year'],
            'synopsis'     => $game['synopsis'],
            'genres'       => array_column($game['genres'], 'id'),
            'platforms'    => array_column($game['platforms'], 'id'),
        ]);
    }

    public function update(int $id): void
    {
        $this->requireAdmin();
        $games = new Game();
        $game = $this->findOr404($id);
        [$data, $errors] = $this->validate($games, $id);
        if ($errors) {
            $this->form($game, $data, $errors, 422);
            return;
        }
        $games->update($id, $data);
        Session::flash('success', '« ' . $data['title'] . ' » a été mis à jour.');
        Response::redirect('/jeu/' . $id);
    }

    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $game = $this->findOr404($id);
        (new Game())->delete($id);
        Session::flash('success', '« ' . $game['title'] . ' » a été supprimé du catalogue.');
        Response::redirect('/admin/jeux');
    }

    private function findOr404(int $id): array
    {
        $game = (new Game())->find($id);
        if ($game === null) {
            Response::error(404, 'Jeu introuvable.');
        }
        return $game;
    }

    private function form(?array $game, array $data, array $errors = [], int $status = 200): void
    {
        $games = new Game();
        $this->render('admin/form', [
            'title'     => $game ? 'Modifier « ' . $game['title'] . ' »' : 'Ajouter un jeu',
            'game'      => $game,
            'data'      => $data,
            'errors'    => $errors,
            'genres'    => $games->genres(),
            'platforms' => $games->platforms(),
        ], $status);
    }

    private function validate(Game $games, int $exceptId = 0): array
    {
        $maxYear = (int) date('Y') + 2;
        $data = [
            'title'        => Request::string('title', 200),
            'developer'    => Request::string('developer', 200),
            'release_year' => Request::int('release_year'),
            'synopsis'     => Request::string('synopsis', 3000),
            'genres'       => $games->existingIds('genres', Request::intList('genres')),
            'platforms'    => $games->existingIds('platforms', Request::intList('platforms')),
        ];

        // La plateforme principale (badge de la jaquette) passe en tête.
        $primary = Request::int('primary_platform');
        if (in_array($primary, $data['platforms'], true)) {
            $data['platforms'] = [$primary, ...array_values(array_diff($data['platforms'], [$primary]))];
        }

        $errors = [];
        $len = mb_strlen($data['title']);
        if ($len < 2 || $len > 120) {
            $errors['title'] = 'Le titre doit contenir entre 2 et 120 caractères.';
        } elseif ($games->titleExists($data['title'], $exceptId)) {
            $errors['title'] = 'Ce jeu est déjà au catalogue.';
        }
        $len = mb_strlen($data['developer']);
        if ($len < 2 || $len > 120) {
            $errors['developer'] = 'Le studio doit contenir entre 2 et 120 caractères.';
        }
        if ($data['release_year'] < 1970 || $data['release_year'] > $maxYear) {
            $errors['release_year'] = 'Année comprise entre 1970 et ' . $maxYear . '.';
        }
        $len = mb_strlen($data['synopsis']);
        if ($len < 20 || $len > 1000) {
            $errors['synopsis'] = 'Le synopsis doit contenir entre 20 et 1000 caractères.';
        }
        if (!$data['genres']) {
            $errors['genres'] = 'Sélectionnez au moins un genre.';
        }
        if (!$data['platforms']) {
            $errors['platforms'] = 'Sélectionnez au moins une plateforme.';
        }

        return [$data, $errors];
    }
}
