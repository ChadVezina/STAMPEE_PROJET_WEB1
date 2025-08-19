<?php
use App\UI\Form;
$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
  <h1>Modifier mon email</h1>
  <?= Form::open(['action'=> "$base/dashboard/email",'method'=>'POST','attrs'=>['data-confirm'=>'email']]); ?>
  <?= \App\Core\CsrfToken::field(); ?>

  <?= Form::input(['type'=>'email','name'=>'email','label'=>'Nouvel email','required'=>true,'value'=>$user['email'] ?? '']); ?>
  <?= Form::input(['type'=>'email','name'=>'confirm_email','label'=>'Confirmer email','required'=>true]); ?>
  <p class="field__error js-error-confirm-email" role="alert" style="display:none;">Les emails ne correspondent pas.</p>

  <?= Form::password(['name'=>'current_password','label'=>'Mot de passe actuel','required'=>true,'toggle'=>true]); ?>

  <?= Form::actions(['submit'=>'Mettre à jour','cancel'=> "$base/dashboard"]); ?>
  <?= Form::close(); ?>
</section>