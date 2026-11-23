<?php

use App\Core\Auth;
use App\Core\View;
use App\Support\Cover;

?>
<section class="detail">
    <div class="detail__backdrop" aria-hidden="true"><?= Cover::render($game, 'backdrop') ?></div>
    <div class="container detail__grid">
        <div class="detail__cover">
            <?= Cover::render($game, 'xl') ?>
        </div>

        <div class="detail__info">
            <a class="back-link" href="/"><?= icon('arrow') ?> Retour au catalogue</a>
            <p class="detail__genres">
                <?php foreach ($game['genres'] as $genre): ?>
                    <a class="tag tag--accent" href="/?genre=<?= e($genre['slug']) ?>"><?= e($genre['name']) ?></a>
                <?php endforeach; ?>
            </p>
            <h1 class="detail__title"><?= e($game['title']) ?></h1>

            <ul class="facts">
                <li><?= icon('calendar') ?><span><?= (int) $game['release_year'] ?></span></li>
                <li><?= icon('code') ?><span><?= e($game['developer']) ?></span></li>
                <li><?= icon('heart') ?><span><span data-fav-count="<?= (int) $game['id'] ?>"><?= (int) $game['fav_count'] ?></span> coups de cœur</span></li>
            </ul>

            <div class="detail__score" data-score>
                <?= View::fetch('game/_score', ['summary' => $summary]) ?>
            </div>

            <p class="detail__synopsis"><?= e($game['synopsis']) ?></p>

            <div class="detail__platforms">
                <span class="detail__label"><?= icon('layers') ?> Disponible sur</span>
                <?php foreach ($game['platforms'] as $platform): ?>
                    <a class="chip" href="/?plateforme=<?= e($platform['slug']) ?>"><?= e($platform['name']) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="detail__actions">
                <button class="btn btn--fav" type="button" data-fav="<?= (int) $game['id'] ?>" data-fav-label aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>">
                    <?= icon('heart') ?>
                    <span data-fav-text><?= $isFavorite ? 'Dans mes coups de cœur' : 'Ajouter aux coups de cœur' ?></span>
                </button>
                <a class="btn btn--ghost" href="#avis"><?= icon('star') ?> Noter ce jeu</a>
                <?php if (Auth::isAdmin()): ?>
                    <a class="btn btn--ghost" href="/admin/jeux/<?= (int) $game['id'] ?>/modifier"><?= icon('edit') ?> Modifier</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="container section" id="avis">
    <div class="section__head">
        <h2 class="section__title"><?= icon('message') ?> Notes &amp; avis</h2>
    </div>
    <div data-reviews>
        <?= View::fetch('game/_reviews', ['game' => $game, 'summary' => $summary, 'reviews' => $reviews, 'myReview' => $myReview]) ?>
    </div>
</section>

<?php if ($similar): ?>
    <section class="container section">
        <div class="section__head">
            <h2 class="section__title"><?= icon('sparkle') ?> Dans le même esprit</h2>
        </div>
        <div class="grid grid--compact">
            <?= View::fetch('partials/game-grid', ['games' => $similar, 'favorites' => $favorites]) ?>
        </div>
    </section>
<?php endif; ?>
