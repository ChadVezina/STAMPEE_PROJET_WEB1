<?php

use App\Core\CsrfToken;

$basePath = \App\Core\Config::get('app.base_path', ''); ?>
<div class="auth-page">
  <div class="auth-container">
    <div class="card card--auth">
      <div class="card__header">
        <h2 class="card__title">
          <span class="card__icon">✨</span>
          Inscription
        </h2>
        <p class="card__subtitle">Créez votre compte en quelques instants</p>
      </div>

      <form id="form-register" class="form form--auth" action="<?= htmlspecialchars($basePath) ?>/register" method="post">
        <?= CsrfToken::field() ?>

        <div class="field field--with-icon">
          <label class="field__label" for="nom">
            <span class="field__icon">👤</span>
            Nom complet
          </label>
          <input class="field__input" type="text" id="nom" name="nom" required placeholder="Jean Dupont" />
          <p class="field__error">Veuillez entrer votre nom.</p>
        </div>

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
            <input class="field__input" type="password" id="password" name="password" required placeholder="Choisissez un mot de passe sécurisé" />
            <button type="button" class="field__toggle-password" aria-label="Afficher/masquer le mot de passe">
              <span class="toggle-icon">👁️</span>
            </button>
          </div>
          <p class="field__error">Veuillez entrer un mot de passe.</p>
        </div>

        <div class="field field--with-icon field--password">
          <label class="field__label" for="confirm">
            <span class="field__icon">🔐</span>
            Confirmer le mot de passe
          </label>
          <div class="field__input-group">
            <input class="field__input" type="password" id="confirm" name="confirm" required placeholder="Retapez votre mot de passe" />
            <button type="button" class="field__toggle-password" aria-label="Afficher/masquer le mot de passe">
              <span class="toggle-icon">👁️</span>
            </button>
          </div>
          <p class="field__error">Les mots de passe ne correspondent pas.</p>
        </div>

        <div class="form__actions">
          <button type="submit" class="button button--primary button--large form__submit">
            <span class="button__icon">🎯</span>
            Créer mon compte
          </button>
        </div>

        <div class="form__footer">
          <p class="form__note">
            Déjà membre ?
            <a href="<?= htmlspecialchars($basePath) ?>/login" class="form__link">
              Se connecter
            </a>
          </p>

        </div>
      </form>
    </div>
  </div>
</div>