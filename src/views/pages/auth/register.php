<?php use App\Core\CsrfToken; $base = \App\Core\Config::get('app.base_url',''); ?>
<h1>Inscription</h1>
<form id="form-register" action="<?= htmlspecialchars($base) ?>/register" method="post" novalidate>
  <?= CsrfToken::field() ?>
  <div>
    <label for="nom">Nom</label>
    <input required type="text" id="nom" name="nom" minlength="2" maxlength="100" autocomplete="name">
    <small class="error" data-for="nom"></small>
  </div>
  <div>
    <label for="email">Email</label>
    <input required type="email" id="email" name="email" autocomplete="email">
    <small class="error" data-for="email"></small>
  </div>
  <div>
    <label for="password">Mot de passe</label>
    <input required type="password" id="password" name="password" minlength="8" autocomplete="new-password">
    <small class="error" data-for="password"></small>
  </div>
  <div>
    <label for="confirm">Confirmation</label>
    <input required type="password" id="confirm" name="confirm" minlength="8" autocomplete="new-password">
    <small class="error" data-for="confirm"></small>
  </div>
  <button type="submit">Créer mon compte</button>
</form>
<p>Déjà inscrit ? <a href="<?= htmlspecialchars($base) ?>/login">Connexion</a></p>