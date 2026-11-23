<?php use App\Core\Config; ?>
<section class="auth">
    <div class="auth__card panel">
        <div class="auth__head">
            <span class="auth__icon"><?= icon('gamepad') ?></span>
            <h1>Connexion</h1>
            <p class="muted">Retrouvez vos coups de cœur et vos avis.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= icon('info') ?><span><?= e($error) ?></span></div>
        <?php endif; ?>

        <form method="post" action="/connexion" class="form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <label class="field">
                <span class="field__label">Adresse e-mail</span>
                <span class="input-icon"><?= icon('mail') ?><input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required autofocus></span>
            </label>
            <label class="field">
                <span class="field__label">Mot de passe <a class="field__aside" href="/mot-de-passe-oublie">Mot de passe oublié ?</a></span>
                <span class="input-icon"><?= icon('lock') ?><input type="password" name="password" autocomplete="current-password" required></span>
            </label>
            <button class="btn btn--primary btn--block" type="submit">Se connecter</button>
        </form>

        <p class="auth__switch">Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>

        <?php if (Config::get('demo')): ?>
            <div class="demo-box">
                <p class="demo-box__title"><?= icon('sparkle') ?> Comptes de démonstration</p>
                <div class="demo-box__accounts">
                    <button type="button" class="demo-account" data-fill-login="joueur@gamehub.fr">
                        <strong>Joueur</strong><span>joueur@gamehub.fr</span>
                    </button>
                    <button type="button" class="demo-account" data-fill-login="admin@gamehub.fr">
                        <strong>Admin</strong><span>admin@gamehub.fr</span>
                    </button>
                </div>
                <p class="muted demo-box__hint">Mot de passe : <code>password</code></p>
            </div>
        <?php endif; ?>
    </div>
</section>
