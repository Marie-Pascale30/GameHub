<?php use App\Models\PasswordReset; ?>
<section class="auth">
    <div class="auth__card panel">
        <div class="auth__head">
            <span class="auth__icon"><?= icon('key') ?></span>
            <h1>Mot de passe oublié</h1>
            <p class="muted">Indiquez votre e-mail : nous vous enverrons un lien de réinitialisation valable <?= PasswordReset::TTL / 60 ?> minutes.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= icon('info') ?><span><?= e($error) ?></span></div>
        <?php endif; ?>

        <?php if (!empty($sent)): ?>
            <div class="alert alert--success" role="status"><?= icon('check') ?>
                <span>Demande enregistrée. Cette démo n’envoie aucun e-mail : la réinitialisation par lien n’est pas disponible en ligne. Pour tester, utilisez les comptes de démo indiqués sur la page de connexion.</span>
            </div>

            <?php if (!empty($demoLink)): ?>
                <div class="demo-box demo-box--warn" data-demo-reset>
                    <p class="demo-box__title"><?= icon('info') ?> Mode démo — aucun e-mail n’est réellement envoyé</p>
                    <p class="muted">Sur un vrai déploiement, ce lien partirait par e-mail. Il est aussi écrit dans les journaux du serveur. Jeton à usage unique, stocké haché en base.</p>
                    <a class="demo-box__link" href="<?= e($demoLink) ?>" data-reset-link><?= e($demoLink) ?></a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="post" action="/mot-de-passe-oublie" class="form" novalidate>
                <?= csrf_field() ?>
                <label class="field">
                    <span class="field__label">Adresse e-mail</span>
                    <span class="input-icon"><?= icon('mail') ?><input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required autofocus></span>
                </label>
                <button class="btn btn--primary btn--block" type="submit">Envoyer le lien</button>
            </form>
        <?php endif; ?>

        <p class="auth__switch"><a href="/connexion"><?= icon('arrow') ?> Retour à la connexion</a></p>
    </div>
</section>
