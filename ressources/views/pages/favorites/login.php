<?php

/** @var string|null $error */
$base = \App\Core\Config::get('app.base_url');
?>

<section class="lord-login">
    <div class="lord-login__container">
        <div class="lord-login__header">
            <h1 class="lord-login__title">🏰 Accès Lord</h1>
            <p class="lord-login__subtitle">Gestion des Coups de Cœur du Lord</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert--error">
                <strong>Erreur:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="lord-login__form" method="POST" action="<?= $base ?>/lord/login">
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe Lord</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    required
                    placeholder="Entrez le mot de passe secret du Lord...">
            </div>

            <div class="form-actions">
                <button type="submit" class="button button--primary button--lord">
                    👑 Accéder à l'interface Lord
                </button>
            </div>
        </form>

        <div class="lord-login__footer">
            <a href="<?= $base ?>/" class="link link--muted">← Retour à l'accueil</a>
        </div>
    </div>
</section>

<!-- Styles moved to ressources/scss/pages/_lord-login.scss -->