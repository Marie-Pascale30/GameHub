<?php use App\Core\View; ?>
<section class="page-head">
    <div class="container">
        <p class="eyebrow"><?= icon('heart') ?> Ma collection</p>
        <h1 class="page-head__title">Mes coups de cœur</h1>
        <p class="muted" data-fav-total><?= $games ? plural(count($games), 'jeu', 'jeux') . ' qui vous ont marqué.' : '' ?></p>
    </div>
</section>

<section class="container section">
    <?php if ($games): ?>
        <div class="grid" data-fav-page>
            <?= View::fetch('partials/game-grid', ['games' => $games, 'favorites' => $favorites]) ?>
        </div>
    <?php endif; ?>
    <div class="empty" data-fav-empty <?= $games ? 'hidden' : '' ?>>
        <?= icon('heart', 'icon empty__icon') ?>
        <h2>Aucun coup de cœur pour l’instant</h2>
        <p>Cliquez sur le cœur d’une jaquette pour retrouver le jeu ici.</p>
        <a class="btn btn--primary" href="/">Parcourir le catalogue</a>
    </div>
</section>
