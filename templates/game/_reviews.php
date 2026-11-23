<?php

use App\Core\Auth;
use App\Models\Review;

$me = Auth::user();
$max = max(1, ...array_values($summary['distribution']));
?>
<div class="reviews">
    <aside class="reviews__side">
        <div class="panel summary">
            <div class="summary__top">
                <strong class="summary__avg"><?= $summary['average'] !== null ? format_rating($summary['average']) : '—' ?></strong>
                <div>
                    <?= stars((float) ($summary['average'] ?? 0)) ?>
                    <p class="muted"><?= $summary['count'] ? plural($summary['count'], 'avis', 'avis') : 'Aucun avis pour le moment' ?></p>
                </div>
            </div>
            <ul class="bars">
                <?php foreach ($summary['distribution'] as $level => $n): ?>
                    <li>
                        <span class="bars__label"><?= (int) $level ?> <?= icon('star', 'icon icon--star') ?></span>
                        <progress max="<?= (int) $max ?>" value="<?= (int) $n ?>" aria-label="<?= plural($n, 'avis', 'avis') ?> à <?= (int) $level ?> étoiles"></progress>
                        <span class="bars__n"><?= (int) $n ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="panel">
            <?php if ($me): ?>
                <h3 class="panel__title"><?= $myReview ? 'Modifier mon avis' : 'Donner mon avis' ?></h3>
                <form class="review-form" method="post" action="/api/jeux/<?= (int) $game['id'] ?>/avis" data-review-form>
                    <?= csrf_field() ?>
                    <fieldset class="star-input">
                        <legend class="sr-only">Votre note sur 5</legend>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="rate-<?= $i ?>" name="rating" value="<?= $i ?>" <?= (int) ($myReview['rating'] ?? 0) === $i ? 'checked' : '' ?> required>
                            <label for="rate-<?= $i ?>" title="<?= plural($i, 'étoile') ?>"><?= icon('star') ?><span class="sr-only"><?= plural($i, 'étoile') ?></span></label>
                        <?php endfor; ?>
                    </fieldset>
                    <label class="field">
                        <span class="field__label">Votre avis <span class="muted">(facultatif)</span></span>
                        <textarea name="body" rows="4" maxlength="<?= Review::BODY_MAX ?>" placeholder="Qu’avez-vous pensé de ce jeu ?" data-counter><?= e($myReview['body'] ?? '') ?></textarea>
                        <span class="field__hint" data-counter-out><?= mb_strlen((string) ($myReview['body'] ?? '')) ?> / <?= Review::BODY_MAX ?></span>
                    </label>
                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit"><?= icon('check') ?> <?= $myReview ? 'Mettre à jour' : 'Publier' ?></button>
                    </div>
                </form>
                <?php if ($myReview): ?>
                    <form method="post" action="/api/jeux/<?= (int) $game['id'] ?>/avis/supprimer" data-review-delete data-confirm="Supprimer votre avis ?">
                        <?= csrf_field() ?>
                        <button class="btn btn--danger-ghost btn--sm" type="submit"><?= icon('trash') ?> Supprimer mon avis</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <h3 class="panel__title">Vous y avez joué ?</h3>
                <p class="muted">Connectez-vous pour noter ce jeu et partager votre avis.</p>
                <a class="btn btn--primary" href="/connexion?next=<?= rawurlencode('/jeu/' . (int) $game['id'] . '#avis') ?>">Se connecter</a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="reviews__list">
        <?php if (!$reviews): ?>
            <div class="empty empty--inline">
                <?= icon('message', 'icon empty__icon') ?>
                <p>Aucun avis pour l’instant. Lancez la discussion !</p>
            </div>
        <?php endif; ?>
        <?php foreach ($reviews as $review): ?>
            <?php $mine = $me && (int) $review['user_id'] === (int) $me['id']; ?>
            <article class="review<?= $mine ? ' review--mine' : '' ?>">
                <span class="avatar avatar--t<?= avatar_tone($review['username']) ?>"><?= e(initials($review['username'])) ?></span>
                <div class="review__body">
                    <header class="review__head">
                        <strong><?= e($review['username']) ?></strong>
                        <?php if ($mine): ?><span class="badge">Vous</span><?php endif; ?>
                        <?= stars((float) $review['rating'], 'stars stars--sm') ?>
                        <time class="muted" datetime="<?= e($review['updated_at'] ?? $review['created_at']) ?>"><?= e(time_ago($review['updated_at'] ?? $review['created_at'])) ?></time>
                    </header>
                    <?php if ($review['body'] !== null && $review['body'] !== ''): ?>
                        <p><?= nl2br(e($review['body'])) ?></p>
                    <?php else: ?>
                        <p class="muted review__nobody">Note sans commentaire.</p>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
