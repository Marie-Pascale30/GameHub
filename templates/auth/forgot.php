<?php use App\Models\PasswordReset; ?>
<section class="auth">
    <div class="auth__card panel">
        <div class="auth__head">
            <span class="auth__icon"><?= icon('key') ?></span>
            <h1>Mot de passe oublié</h1>
            <p class="muted">Cette démo n’envoie pas d’e-mail : la réinitialisation par lien n’est pas disponible en ligne. Utilisez les comptes de démo indiqués sur la page de connexion.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= icon('info') ?><span><?= e($error) ?></span></div>
        <?php endif; ?>

        <?php if (!empty($sent)): ?>
            <div class="alert alert--success" role="status"><?= icon('check') ?>
                <span>Demande enregistrée. Cette démo n’envoie aucun e-mail : la réinitialisation par lien n’est pas disponible en ligne. Pour tester, utilisez les comptes de démo indiqués sur la page de connexion.</span>
            </div>
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
