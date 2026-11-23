<?php

use App\Core\View;

foreach ($games as $game) {
    echo View::fetch('partials/game-card', ['game' => $game, 'favorites' => $favorites]);
}
