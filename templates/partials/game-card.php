<?php

use App\Support\Cover;

/** @var array $game @var array $favorites */
$isFav = isset($favorites[$game['id']]);
?>
<article class="card" data-game-id="<?= (int) $game['id'] ?>">
    <a class="card__link" href="/jeu/<?= (int) $game['id'] ?>">
        <?= Cover::render($game) ?>
        <span class="card__body">
            <span class="card__title"><?= e($game['title']) ?></span>
            <span class="card__meta"><?= e($game['release_year']) ?> · <?= e($game['developer']) ?></span>
            <span class="card__tags">
                <?php foreach (array_slice($game['genres'], 0, 2) as $genre): ?>
                    <span class="tag"><?= e($genre['name']) ?></span>
                <?php endforeach; ?>
            </span>
            <span class="card__foot">
                <?php if ($game['avg_rating'] !== null): ?>
                    <span class="score" title="Note moyenne sur <?= plural($game['review_count'], 'avis', 'avis') ?>">
                        <?= icon('star', 'icon icon--star') ?><strong><?= format_rating($game['avg_rating']) ?></strong>
                        <small>(<?= (int) $game['review_count'] ?>)</small>
                    </span>
                <?php else: ?>
                    <span class="score score--none">Pas encore noté</span>
                <?php endif; ?>
                <span class="card__favs" title="Coups de cœur"><?= icon('heart') ?><span data-fav-count="<?= (int) $game['id'] ?>"><?= (int) $game['fav_count'] ?></span></span>
            </span>
        </span>
    </a>
    <button class="fav-btn" type="button" data-fav="<?= (int) $game['id'] ?>" aria-pressed="<?= $isFav ? 'true' : 'false' ?>"
            aria-label="<?= $isFav ? 'Retirer' : 'Ajouter' ?> « <?= e($game['title']) ?> » <?= $isFav ? 'de' : 'à' ?> mes coups de cœur">
        <?= icon('heart') ?>
    </button>
</article>
