<?php use App\Core\CsrfToken; $base = \App\Core\Config::get('app.base_url',''); ?>
<h1>Connexion</h1>
<form id="form-login" action="<?= htmlspecialchars($base) ?>/login" method="post" novalidate>
  <?= CsrfToken::field() ?>
  <div>
    <label for="email">Email</label>
    <input required type="email" id="email" name="email" autocomplete="email">
    <small class="error" data-for="email"></small>
  </div>
  <div>
    <label for="password">Mot de passe</label>
    <input required type="password" id="password" name="password" autocomplete="current-password" minlength="8">
    <small class="error" data-for="password"></small>
  </div>
  <button type="submit">Se connecter</button>
</form>
<p>Pas encore de compte ? <a href="<?= htmlspecialchars($base) ?>/register">Inscription</a></p>