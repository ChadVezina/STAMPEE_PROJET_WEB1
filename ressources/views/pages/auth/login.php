<?php

use App\Core\CsrfToken;

$basePath = \App\Core\Config::get('app.base_path', ''); ?>
<div class="auth-page">
  <div class="auth-container">
    <div class="card card--auth">
      <div class="card__header">
        <h2 class="card__title">
          <span class="card__icon">🔐</span>
          Connexion
        </h2>
        <p class="card__subtitle">Accédez à votre collection</p>
      </div>

      <form id="form-login" class="form form--auth" action="<?= htmlspecialchars($basePath) ?>/login" method="post">
        <?= CsrfToken::field() ?>

        <div class="field field--with-icon">
          <label class="field__label" for="email">
            <span class="field__icon">📧</span>
            Adresse e-mail
          </label>
          <input class="field__input" type="email" id="email" name="email" required placeholder="votre.email@exemple.com" />
          <p class="field__error">Veuillez entrer une adresse e-mail valide.</p>
        </div>

        <div class="field field--with-icon field--password">
          <label class="field__label" for="password">
            <span class="field__icon">🔒</span>
            Mot de passe
          </label>
          <div class="field__input-group">
            <input class="field__input" type="password" id="password" name="password" required placeholder="Votre mot de passe" />
            <button type="button" class="field__toggle-password" aria-label="Afficher/masquer le mot de passe">
              <span class="toggle-icon">👁️</span>
            </button>
          </div>
          <p class="field__error">Veuillez entrer votre mot de passe.</p>
        </div>

        <div class="form__actions">
          <button type="submit" class="button button--primary button--large form__submit">
            <span class="button__icon">🚀</span>
            Se connecter
          </button>
        </div>

        <div class="form__footer">
          <p class="form__note">
            Pas encore de compte ?
            <a href="<?= htmlspecialchars($basePath) ?>/register" class="form__link">
              Créer un compte
            </a>
          </p>
          <a href="#" class="form__forgot">Mot de passe oublié ?</a>
        </div>
      </form>
    </div>
  </div>
</div>