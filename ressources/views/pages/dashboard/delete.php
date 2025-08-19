<?php
use App\UI\Form;
$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
  <h1>Supprimer mon compte</h1>
  <p class="field__hint">Action irréversible. Vos données seront supprimées définitivement.</p>

  <?= Form::open(['action'=> "$base/dashboard/supprimer",'method'=>'POST','attrs'=>['data-guard'=>'delete']]); ?>
  <?= \App\Core\CsrfToken::field(); ?>

  <?= Form::password(['name'=>'current_password','label'=>'Mot de passe actuel','required'=>true,'toggle'=>true]); ?>
  <?= Form::input(['name'=>'confirm_phrase','label'=>'Saisir « SUPPRIMER » pour confirmer','required'=>true,'placeholder'=>'SUPPRIMER']); ?>

  <?= Form::actions(['submit'=>'Supprimer définitivement','cancel'=> "$base/dashboard"]); ?>
  <?= Form::close(); ?>
</section>