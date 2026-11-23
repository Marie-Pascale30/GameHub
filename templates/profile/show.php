<?php

use App\Core\View;

$maxGenre = $genres ? max(array_map(static fn ($g) => (int) $g['n'], $genres)) : 1;
?>
<section class="page-head page-head--profile">
    <div class="container profile-head">
        <span class="avatar avatar--xl avatar--t<?= avatar_tone($user['username']) ?>"><?= e(initials($user['username'])) ?></span>
        <div class="profile-head__id">
            <p class="eyebrow"><?= $user['role'] === 'admin' ? icon('shield') . ' Administrateur' : icon('gamepad') . ' Joueur' ?></p>
            <h1 class="page-head__title"><?= e($user['username']) ?></h1>
            <p class="muted"><?= e($user['email']) ?> · membre depuis <?= e(fr_month_year($user['created_at'])) ?></p>
        </div>
    </div>
</section>

<section class="container section">
    <div class="stat-grid">
        <div class="stat"><span class="stat__icon stat__icon--pink"><?= icon('heart') ?></span><strong><?= (int) $stats['favorites'] ?></strong><span>Coups de cœur</span></div>
        <div class="stat"><span class="stat__icon stat__icon--amber"><?= icon('star') ?></span><strong><?= (int) $stats['reviews'] ?></strong><span>Jeux notés</span></div>
        <div class="stat"><span class="stat__icon stat__icon--cyan"><?= icon('trophy') ?></span><strong><?= $stats['avg_rating'] !== null ? format_rating($stats['avg_rating']) : '—' ?></strong><span>Note moyenne donnée</span></div>
        <div class="stat"><span class="stat__icon stat__icon--violet"><?= icon('message') ?></span><strong><?= (int) $stats['written'] ?></strong><span>Avis rédigés</span></div>
    </div>

    <div class="cols">
        <section class="panel">
            <h2 class="panel__title"><?= icon('tag') ?> Genres préférés</h2>
            <?php if ($genres): ?>
                <ul class="genre-bars">
                    <?php foreach ($genres as $genre): ?>
                        <li>
                            <a href="/?genre=<?= e($genre['slug']) ?>"><?= e($genre['name']) ?></a>
                            <progress max="<?= (int) $maxGenre ?>" value="<?= (int) $genre['n'] ?>" aria-label="<?= e($genre['name']) ?>"></progress>
                            <span class="muted"><?= (int) $genre['n'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="muted panel__note">Calculé à partir de vos coups de cœur et des jeux notés 4 étoiles ou plus.</p>
            <?php else: ?>
                <p class="muted">Ajoutez des coups de cœur pour découvrir vos genres de prédilection.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2 class="panel__title"><?= icon('clock') ?> Activité récente</h2>
            <?php if ($activity): ?>
                <ol class="timeline">
                    <?php foreach ($activity as $item): ?>
                        <li class="timeline__item timeline__item--<?= e($item['kind']) ?>">
                            <span class="timeline__dot"><?= icon($item['kind'] === 'favorite' ? 'heart' : 'star') ?></span>
                            <span>
                                <?php if ($item['kind'] === 'favorite'): ?>
                                    Coup de cœur pour <a href="/jeu/<?= (int) $item['game_id'] ?>"><?= e($item['title']) ?></a>
                                <?php else: ?>
                                    A noté <a href="/jeu/<?= (int) $item['game_id'] ?>"><?= e($item['title']) ?></a> <?= stars((float) $item['rating'], 'stars stars--sm') ?>
                                <?php endif; ?>
                                <time class="muted" datetime="<?= e($item['happened_at']) ?>"><?= e(time_ago($item['happened_at'])) ?></time>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="muted">Rien pour l’instant : notez un jeu ou ajoutez un coup de cœur.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="cols" id="parametres">
        <section class="panel">
            <h2 class="panel__title"><?= icon('user') ?> Modifier le pseudo</h2>
            <form method="post" action="/profil/pseudo" class="form" novalidate>
                <?= csrf_field() ?>
                <label class="field<?= isset($errors['username']) ? ' field--error' : '' ?>">
                    <span class="field__label">Pseudo</span>
                    <input type="text" name="username" value="<?= e($old['username'] ?? $user['username']) ?>" minlength="3" maxlength="30" required>
                    <?php if (isset($errors['username'])): ?><span class="field__error"><?= e($errors['username']) ?></span><?php endif; ?>
                    <span class="field__hint">3 à 30 caractères : lettres, chiffres, « _ », « . » ou « - ».</span>
                </label>
                <button class="btn btn--primary" type="submit">Enregistrer le pseudo</button>
            </form>
        </section>

        <section class="panel">
            <h2 class="panel__title"><?= icon('lock') ?> Changer le mot de passe</h2>
            <form method="post" action="/profil/mot-de-passe" class="form" novalidate>
                <?= csrf_field() ?>
                <label class="field<?= isset($errors['current_password']) ? ' field--error' : '' ?>">
                    <span class="field__label">Mot de passe actuel</span>
                    <input type="password" name="current_password" autocomplete="current-password" required>
                    <?php if (isset($errors['current_password'])): ?><span class="field__error"><?= e($errors['current_password']) ?></span><?php endif; ?>
                </label>
                <label class="field<?= isset($errors['new_password']) ? ' field--error' : '' ?>">
                    <span class="field__label">Nouveau mot de passe</span>
                    <input type="password" name="new_password" id="profile-password" autocomplete="new-password" required>
                    <?php if (isset($errors['new_password'])): ?><span class="field__error"><?= e($errors['new_password']) ?></span><?php endif; ?>
                </label>
                <?= View::fetch('partials/password-rules', ['for' => 'profile-password']) ?>
                <label class="field<?= isset($errors['new_password_confirm']) ? ' field--error' : '' ?>">
                    <span class="field__label">Confirmation</span>
                    <input type="password" name="new_password_confirm" autocomplete="new-password" required>
                    <?php if (isset($errors['new_password_confirm'])): ?><span class="field__error"><?= e($errors['new_password_confirm']) ?></span><?php endif; ?>
                </label>
                <button class="btn btn--primary" type="submit">Changer le mot de passe</button>
            </form>
        </section>
    </div>
</section>
