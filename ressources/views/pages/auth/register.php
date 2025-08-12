<?php

use App\Core\CsrfToken;

$basePath = \App\Core\Config::get('app.base_path', ''); ?>
<div class="auth-page">
  <div class="card card--auth">
    <h1 class="card__title">Inscription</h1>
    <form class="form form--auth" action="/Stampee/register" method="post">
      <?= CsrfToken::field() ?>
      <div class="field">
        <label class="field__label" for="fullname">Nom complet</label>
        <input class="field__input" type="text" id="fullname" name="fullname" required />
        <p class="field__error">Veuillez entrer votre nom.</p>
      </div>
      <div class="field">
        <label class="field__label" for="email">Adresse e-mail</label>
        <input class="field__input" type="email" id="email" name="email" required />
        <p class="field__error">Veuillez entrer une adresse e-mail valide.</p>
      </div>
      <div class="field">
        <label class="field__label" for="password">Mot de passe</label>
        <input class="field__input" type="password" id="password" name="password" required />
        <p class="field__error">Veuillez entrer un mot de passe.</p>
      </div>
      <div class="field">
        <label class="field__label" for="confirm">Confirmer le mot de passe</label>
        <input class="field__input" type="password" id="confirm" name="confirm" required />
        <p class="field__error">Les mots de passe ne correspondent pas.</p>
      </div>
      <button type="submit" class="button button--primary form__submit">S'inscrire</button>
      <p class="form__note">
        Déjà membre ? <a href="/Stampee/login">Connexion</a>
      </p>
    </form>
  </div>
</div>