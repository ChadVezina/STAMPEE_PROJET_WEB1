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
          <div class="field__input-wrapper">
            <input class="field__input" type="password" id="password" name="password" required placeholder="Votre mot de passe" />
            <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
              <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
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