<?php

use App\Core\View;

$hasFilters = $filters['q'] !== '' || $filters['genre'] !== '' || $filters['platform'] !== '' || $filters['decade'] !== 0;
$query = static function (array $overrides) use ($filters): string {
    $params = array_filter([
        'q'          => $filters['q'],
        'genre'      => $filters['genre'],
        'plateforme' => $filters['platform'],
        'decennie'   => $filters['decade'] ?: '',
        'tri'        => $filters['sort'] !== 'populaire' ? $filters['sort'] : '',
    ] + [], static fn ($v) => $v !== '' && $v !== null);
    return '/?' . http_build_query(array_merge($params, $overrides));
};
?>
<section class="hero">
    <div class="container hero__inner">
        <div class="hero__copy">
            <p class="eyebrow"><?= icon('sparkle') ?> Catalogue communautaire</p>
            <h1 class="hero__title">Trouvez votre prochain <span class="text-grad">coup de cœur</span>.</h1>
            <p class="hero__lead">Des classiques de l’arcade aux mondes ouverts modernes : explorez, notez et gardez sous la main les jeux qui ont compté pour vous.</p>
        </div>
        <dl class="hero__stats">
            <div><dt>Jeux</dt><dd><?= (int) $stats['games'] ?></dd></div>
            <div><dt>Avis</dt><dd><?= (int) $stats['reviews'] ?></dd></div>
            <div><dt>Joueurs</dt><dd><?= (int) $stats['players'] ?></dd></div>
        </dl>
    </div>
</section>

<section class="container catalog">
    <form class="filters" action="/" method="get" role="search" data-filters>
        <label class="search">
            <?= icon('search') ?>
            <span class="sr-only">Rechercher</span>
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Rechercher un jeu ou un studio…" maxlength="60" autocomplete="off" data-search>
        </label>

        <div class="filters__row">
            <label class="field-select">
                <span class="field-select__label">Genre</span>
                <select name="genre">
                    <option value="">Tous</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= e($genre['slug']) ?>" <?= $filters['genre'] === $genre['slug'] ? 'selected' : '' ?>><?= e($genre['name']) ?> (<?= (int) $genre['n'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field-select">
                <span class="field-select__label">Plateforme</span>
                <select name="plateforme">
                    <option value="">Toutes</option>
                    <?php foreach ($platforms as $platform): ?>
                        <?php if ((int) $platform['n'] > 0): ?>
                            <option value="<?= e($platform['slug']) ?>" <?= $filters['platform'] === $platform['slug'] ? 'selected' : '' ?>><?= e($platform['name']) ?> (<?= (int) $platform['n'] ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field-select">
                <span class="field-select__label">Décennie</span>
                <select name="decennie">
                    <option value="">Toutes</option>
                    <?php foreach ($decades as $decade): ?>
                        <option value="<?= (int) $decade ?>" <?= $filters['decade'] === $decade ? 'selected' : '' ?>>Années <?= $decade >= 2000 ? (int) $decade : substr((string) $decade, 2) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field-select">
                <span class="field-select__label">Trier par</span>
                <select name="tri">
                    <?php foreach ($sorts as $key => $sort): ?>
                        <option value="<?= e($key) ?>" <?= $filters['sort'] === $key ? 'selected' : '' ?>><?= e($sort['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn--primary no-js-only" type="submit"><?= icon('filter') ?> Filtrer</button>
        </div>
    </form>

    <div class="results-bar">
        <p class="results-bar__count" data-results-count aria-live="polite">
            <strong><?= (int) $result['total'] ?></strong> <?= $result['total'] > 1 ? 'jeux trouvés' : 'jeu trouvé' ?>
        </p>
        <a class="link-muted" href="/" data-reset <?= $hasFilters ? '' : 'hidden' ?>><?= icon('x') ?> Réinitialiser les filtres</a>
    </div>

    <div class="grid" data-grid data-page="<?= (int) $result['page'] ?>" data-pages="<?= (int) $result['pages'] ?>">
        <?= View::fetch('partials/game-grid', ['games' => $result['items'], 'favorites' => $favorites]) ?>
    </div>

    <div class="empty" data-empty <?= $result['total'] === 0 ? '' : 'hidden' ?>>
        <?= icon('search', 'icon empty__icon') ?>
        <h2>Aucun jeu ne correspond</h2>
        <p>Essayez un autre mot-clé ou retirez un filtre.</p>
        <a class="btn btn--ghost" href="/">Voir tout le catalogue</a>
    </div>

    <div class="loadmore" data-loadmore>
        <nav class="pager no-js-only" aria-label="Pagination">
            <?php if ($result['page'] > 1): ?>
                <a class="btn btn--ghost" href="<?= e($query(['page' => $result['page'] - 1])) ?>">← Précédent</a>
            <?php endif; ?>
            <span>Page <?= (int) $result['page'] ?> / <?= (int) $result['pages'] ?></span>
            <?php if ($result['page'] < $result['pages']): ?>
                <a class="btn btn--ghost" href="<?= e($query(['page' => $result['page'] + 1])) ?>">Suivant →</a>
            <?php endif; ?>
        </nav>
        <button class="btn btn--ghost js-only" type="button" data-more <?= $result['page'] < $result['pages'] ? '' : 'hidden' ?>>Charger plus de jeux</button>
        <div class="loadmore__sentinel" data-sentinel aria-hidden="true"></div>
    </div>
</section>
