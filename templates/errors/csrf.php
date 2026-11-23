<section class="container error-page">
    <span class="error-page__code"><?= (int) $status ?></span>
    <?= icon('shield', 'icon error-page__icon') ?>
    <h1>Session expirée</h1>
    <p class="muted"><?= e($message) ?></p>
    <a class="btn btn--primary" href="/">Retour au catalogue</a>
</section>
