<section class="container error-page">
    <span class="error-page__code"><?= (int) $status ?></span>
    <?= icon('clock', 'icon error-page__icon') ?>
    <h1>Doucement !</h1>
    <p class="muted"><?= e($message) ?></p>
    <a class="btn btn--primary" href="/">Retour au catalogue</a>
</section>
