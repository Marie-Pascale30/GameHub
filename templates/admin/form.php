<?php

use App\Support\Cover;

$err = static fn (string $k): string => isset($errors[$k]) ? '<span class="field__error">' . e($errors[$k]) . '</span>' : '';
$selectedPlatforms = array_map('intval', $form['platforms']);
$selectedGenres = array_map('intval', $form['genres']);
$primary = $selectedPlatforms[0] ?? 0;
?>
<section class="page-head">
    <div class="container">
        <a class="back-link" href="/admin/jeux"><?= icon('arrow') ?> Gestion du catalogue</a>
        <h1 class="page-head__title"><?= $game ? 'Modifier « ' . e($game['title']) . ' »' : 'Ajouter un jeu' ?></h1>
        <p class="muted">La jaquette est générée automatiquement à partir du titre et de la plateforme principale.</p>
    </div>
</section>

<section class="container section">
    <?php if ($errors): ?>
        <div class="alert alert--error" role="alert"><?= icon('info') ?><span>Le formulaire contient <?= plural(count($errors), 'erreur') ?>.</span></div>
    <?php endif; ?>

    <form method="post" action="<?= $game ? '/admin/jeux/' . (int) $game['id'] : '/admin/jeux' ?>" class="admin-form" novalidate>
        <?= csrf_field() ?>
        <div class="admin-form__main panel">
            <div class="form-grid">
                <label class="field field--wide<?= isset($errors['title']) ? ' field--error' : '' ?>">
                    <span class="field__label">Titre</span>
                    <input type="text" name="title" value="<?= e($form['title']) ?>" maxlength="120" required>
                    <?= $err('title') ?>
                </label>
                <label class="field<?= isset($errors['developer']) ? ' field--error' : '' ?>">
                    <span class="field__label">Studio</span>
                    <input type="text" name="developer" value="<?= e($form['developer']) ?>" maxlength="120" required>
                    <?= $err('developer') ?>
                </label>
                <label class="field<?= isset($errors['release_year']) ? ' field--error' : '' ?>">
                    <span class="field__label">Année de sortie</span>
                    <input type="number" name="release_year" value="<?= e($form['release_year'] ?: '') ?>" min="1970" max="<?= (int) date('Y') + 2 ?>" required>
                    <?= $err('release_year') ?>
                </label>
                <label class="field field--wide<?= isset($errors['synopsis']) ? ' field--error' : '' ?>">
                    <span class="field__label">Synopsis</span>
                    <textarea name="synopsis" rows="5" maxlength="1000" required data-counter><?= e($form['synopsis']) ?></textarea>
                    <span class="field__hint" data-counter-out><?= mb_strlen((string) $form['synopsis']) ?> / 1000</span>
                    <?= $err('synopsis') ?>
                </label>
            </div>

            <fieldset class="checks<?= isset($errors['genres']) ? ' field--error' : '' ?>">
                <legend class="field__label">Genres</legend>
                <div class="checks__list">
                    <?php foreach ($genres as $genre): ?>
                        <label class="check-chip">
                            <input type="checkbox" name="genres[]" value="<?= (int) $genre['id'] ?>" <?= in_array((int) $genre['id'], $selectedGenres, true) ? 'checked' : '' ?>>
                            <span><?= e($genre['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?= $err('genres') ?>
            </fieldset>

            <fieldset class="checks<?= isset($errors['platforms']) ? ' field--error' : '' ?>">
                <legend class="field__label">Plateformes</legend>
                <div class="checks__list">
                    <?php foreach ($platforms as $platform): ?>
                        <label class="check-chip">
                            <input type="checkbox" name="platforms[]" value="<?= (int) $platform['id'] ?>" <?= in_array((int) $platform['id'], $selectedPlatforms, true) ? 'checked' : '' ?>>
                            <span><?= e($platform['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?= $err('platforms') ?>
            </fieldset>

            <label class="field field--narrow">
                <span class="field__label">Plateforme principale (badge de la jaquette)</span>
                <select name="primary_platform">
                    <option value="0">Première cochée</option>
                    <?php foreach ($platforms as $platform): ?>
                        <option value="<?= (int) $platform['id'] ?>" <?= $primary === (int) $platform['id'] ? 'selected' : '' ?>><?= e($platform['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit"><?= icon('check') ?> <?= $game ? 'Enregistrer les modifications' : 'Ajouter au catalogue' ?></button>
                <a class="btn btn--ghost" href="/admin/jeux">Annuler</a>
            </div>
        </div>

        <aside class="admin-form__side">
            <p class="field__label">Aperçu de la jaquette</p>
            <?php if ($game): ?>
                <?= Cover::render($game, 'xl') ?>
            <?php else: ?>
                <div class="cover-placeholder"><?= icon('sparkle') ?><span>La jaquette sera générée à l’enregistrement.</span></div>
            <?php endif; ?>
        </aside>
    </form>
</section>
