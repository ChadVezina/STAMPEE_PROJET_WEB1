<?php
use App\UI\Form;
$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
  <h1>Modifier mon mot de passe</h1>
  <?= Form::open(['action'=> "$base/dashboard/password",'method'=>'POST','attrs'=>['data-confirm'=>'password']]); ?>
  <?= \App\Core\CsrfToken::field(); ?>

  <?= Form::password(['name'=>'current_password','label'=>'Mot de passe actuel','required'=>true,'toggle'=>true]); ?>
  <?= Form::password(['name'=>'new_password','label'=>'Nouveau mot de passe','required'=>true,'toggle'=>true,'hint'=>'8 caractères minimum']); ?>
  <?= Form::password(['name'=>'confirm_password','label'=>'Confirmer le mot de passe','required'=>true,'toggle'=>true]); ?>
  <p class="field__error js-error-confirm-password" role="alert" style="display:none;">Les mots de passe ne correspondent pas.</p>

  <?= Form::actions(['submit'=>'Mettre à jour','cancel'=> "$base/dashboard"]); ?>
  <?= Form::close(); ?>
</section>