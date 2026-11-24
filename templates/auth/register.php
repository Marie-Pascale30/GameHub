<?php use App\Core\View; ?>
<section class="auth">
    <div class="auth__card panel">
        <div class="auth__head">
            <span class="auth__icon"><?= icon('sparkle') ?></span>
            <h1>Créer un compte</h1>
            <p class="muted">Gratuit, et vos données restent les vôtres.</p>
        </div>

        <div class="alert alert--demo signup-notice" role="note"><?= icon('info') ?>
            <span><strong>Site de démonstration :</strong> n’utilisez pas de vraies données (prenez un pseudo et une adresse inventés). Tout est effacé à chaque redémarrage du serveur.
                En créant un compte, vous acceptez le traitement décrit dans les <a href="/mentions-legales" target="_blank" rel="noopener">mentions légales &amp; confidentialité</a>. Vous pourrez supprimer votre compte depuis votre profil.</span>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert--error" role="alert"><?= icon('info') ?><span>Merci de corriger les champs signalés.</span></div>
        <?php endif; ?>

        <form method="post" action="/inscription" class="form" novalidate>
            <?= csrf_field() ?>
            <label class="field<?= isset($errors['username']) ? ' field--error' : '' ?>">
                <span class="field__label">Pseudo</span>
                <span class="input-icon"><?= icon('user') ?><input type="text" name="username" value="<?= e($old['username'] ?? '') ?>" minlength="3" maxlength="30" autocomplete="username" required autofocus></span>
                <?php if (isset($errors['username'])): ?><span class="field__error"><?= e($errors['username']) ?></span><?php endif; ?>
            </label>
            <label class="field<?= isset($errors['email']) ? ' field--error' : '' ?>">
                <span class="field__label">Adresse e-mail</span>
                <span class="input-icon"><?= icon('mail') ?><input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required></span>
                <?php if (isset($errors['email'])): ?><span class="field__error"><?= e($errors['email']) ?></span><?php endif; ?>
            </label>
            <label class="field<?= isset($errors['password']) ? ' field--error' : '' ?>">
                <span class="field__label">Mot de passe</span>
                <span class="input-icon"><?= icon('lock') ?><input type="password" name="password" id="reg-password" autocomplete="new-password" required></span>
                <?php if (isset($errors['password'])): ?><span class="field__error" data-error="password"><?= e($errors['password']) ?></span><?php endif; ?>
            </label>
            <?= View::fetch('partials/password-rules', ['for' => 'reg-password']) ?>
            <label class="field<?= isset($errors['password_confirm']) ? ' field--error' : '' ?>">
                <span class="field__label">Confirmation</span>
                <span class="input-icon"><?= icon('lock') ?><input type="password" name="password_confirm" autocomplete="new-password" required></span>
                <?php if (isset($errors['password_confirm'])): ?><span class="field__error"><?= e($errors['password_confirm']) ?></span><?php endif; ?>
            </label>
            <button class="btn btn--primary btn--block" type="submit">Créer mon compte</button>
        </form>

        <p class="auth__switch">Déjà inscrit ? <a href="/connexion">Se connecter</a></p>
    </div>
</section>
