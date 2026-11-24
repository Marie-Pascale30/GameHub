<?php use App\Support\Cover; ?>
<section class="page-head">
    <div class="container page-head__row">
        <div>
            <p class="eyebrow"><?= icon('shield') ?> Administration</p>
            <h1 class="page-head__title">Gestion du catalogue</h1>
            <p class="muted"><?= plural(count($games), 'jeu', 'jeux') ?><?= $q !== '' ? ' pour « ' . e($q) . ' »' : '' ?></p>
        </div>
        <a class="btn btn--primary" href="/admin/jeux/nouveau"><?= icon('plus') ?> Ajouter un jeu</a>
    </div>
</section>

<section class="container section">
    <?php if (App\Core\Auth::isDemo()): ?>
        <p class="alert alert--demo" role="note"><?= icon('info') ?><span><strong>Démo :</strong> ajoutez, modifiez ou supprimez des jeux librement. Le catalogue d’origine est restauré à chaque redémarrage du serveur, et l’application fonctionne même si le catalogue est vide.</span></p>
    <?php endif; ?>
    <form class="admin-search" method="get" action="/admin/jeux" role="search">
        <label class="search search--sm">
            <?= icon('search') ?>
            <span class="sr-only">Filtrer</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Filtrer par titre ou studio…">
        </label>
    </form>

    <div class="table-wrap panel">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Jeu</th>
                <th scope="col" class="hide-sm">Genres</th>
                <th scope="col" class="num">Année</th>
                <th scope="col" class="num hide-sm">Note</th>
                <th scope="col" class="num hide-sm">♥</th>
                <th scope="col" class="actions-col"><span class="sr-only">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td>
                        <a class="table__game" href="/jeu/<?= (int) $game['id'] ?>">
                            <?= Cover::render($game, 'thumb') ?>
                            <span><strong><?= e($game['title']) ?></strong><small class="muted"><?= e($game['developer']) ?></small></span>
                        </a>
                    </td>
                    <td class="hide-sm"><?= e(implode(', ', array_column($game['genres'], 'name'))) ?></td>
                    <td class="num"><?= (int) $game['release_year'] ?></td>
                    <td class="num hide-sm"><?= $game['avg_rating'] !== null ? format_rating($game['avg_rating']) : '—' ?></td>
                    <td class="num hide-sm"><?= (int) $game['fav_count'] ?></td>
                    <td class="actions-col">
                        <a class="icon-btn" href="/admin/jeux/<?= (int) $game['id'] ?>/modifier" aria-label="Modifier <?= e($game['title']) ?>" title="Modifier"><?= icon('edit') ?></a>
                        <form method="post" action="/admin/jeux/<?= (int) $game['id'] ?>/supprimer" class="inline-form" data-confirm="Supprimer « <?= e($game['title']) ?> » ? Ses avis et favoris seront aussi supprimés.">
                            <?= csrf_field() ?>
                            <button class="icon-btn icon-btn--danger" type="submit" aria-label="Supprimer <?= e($game['title']) ?>" title="Supprimer"><?= icon('trash') ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
