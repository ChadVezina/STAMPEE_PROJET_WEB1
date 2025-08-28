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

<style>
    .lord-login {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        padding: 2rem;
    }

    .lord-login__container {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
    }

    .lord-login__header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .lord-login__title {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .lord-login__subtitle {
        color: #718096;
        font-size: 1rem;
        margin: 0;
    }

    .lord-login__form {
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-input {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: #1b355a;
        box-shadow: 0 0 0 3px rgba(27, 53, 90, 0.1);
    }

    .form-actions {
        margin-bottom: 1rem;
    }

    .button--lord {
        width: 100%;
        background: #1b355a;
        border: none;
        padding: 1rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        color: white;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .button--lord:hover {
        transform: translateY(-2px);
        background: #152a47;
        box-shadow: 0 10px 20px rgba(27, 53, 90, 0.3);
    }

    .lord-login__footer {
        text-align: center;
    }

    .link--muted {
        color: #718096;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .link--muted:hover {
        color: #4a5568;
    }

    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }

    .alert--error {
        background-color: #fed7d7;
        color: #c53030;
        border: 1px solid #fc8181;
    }
</style>