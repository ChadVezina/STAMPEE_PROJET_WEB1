<?php

use App\Core\CsrfToken;

$basePath = \App\Core\Config::get('app.base_path', ''); ?>
<div class="auth-page">
  <div class="card card--auth">
    <h1 class="card__title">Connexion</h1>
    <form class="form form--auth" action="/Stampee/login" method="post">
      <?= CsrfToken::field() ?>
      <div class="field">
        <label class="field__label" for="email">Adresse e-mail</label>
        <input class="field__input" type="email" id="email" name="email" required />
        <p class="field__error">Veuillez entrer une adresse e-mail valide.</p>
      </div>
      <div class="field">
        <label class="field__label" for="password">Mot de passe</label>
        <input class="field__input" type="password" id="password" name="password" required />
        <p class="field__error">Veuillez entrer votre mot de passe.</p>
      </div>
      <button type="submit" class="button button--primary form__submit">Se connecter</button>
      <p class="form__note">
        Pas encore de compte ? <a href="/Stampee/register">Inscription</a>
      </p>
    </form>
  </div>
</div>