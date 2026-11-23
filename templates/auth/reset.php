<?php use App\Core\View; ?>
<section class="auth">
    <div class="auth__card panel">
        <div class="auth__head">
            <span class="auth__icon"><?= icon('lock') ?></span>
            <h1>Nouveau mot de passe</h1>
        </div>

        <?php if ($invalid): ?>
            <div class="alert alert--error" role="alert"><?= icon('info') ?>
                <span>Ce lien est invalide, a expiré ou a déjà été utilisé.</span>
            </div>
            <a class="btn btn--primary btn--block" href="/mot-de-passe-oublie">Demander un nouveau lien</a>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert"><?= icon('info') ?><span><?= e($error) ?></span></div>
            <?php endif; ?>
            <form method="post" action="/reinitialisation" class="form" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <label class="field">
                    <span class="field__label">Nouveau mot de passe</span>
                    <span class="input-icon"><?= icon('lock') ?><input type="password" name="password" id="reset-password" autocomplete="new-password" required autofocus></span>
                </label>
                <?= View::fetch('partials/password-rules', ['for' => 'reset-password']) ?>
                <label class="field">
                    <span class="field__label">Confirmation</span>
                    <span class="input-icon"><?= icon('lock') ?><input type="password" name="password_confirm" autocomplete="new-password" required></span>
                </label>
                <button class="btn btn--primary btn--block" type="submit">Enregistrer</button>
            </form>
        <?php endif; ?>
    </div>
</section>
