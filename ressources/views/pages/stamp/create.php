<?php
use App\UI\Form;
$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
  <h1>Ajouter un timbre</h1>

  <?= Form::open(['action' => $base . '/timbre/store', 'method' => 'POST']); ?>
  <?= \App\Core\CsrfToken::field(); ?>

  <?= Form::input(['name'=>'name','label'=>'Nom','required'=>true,'placeholder'=>'Nom du timbre']); ?>

  <?= Form::input(['type'=>'date','name'=>'created_at','label'=>'Date de création (si connue)','placeholder'=>'YYYY-MM-DD']); ?>

  <?php
    $opts = ['' => '— Pays d’origine —'];
    foreach (($countries ?? []) as $c) { $opts[$c['iso2']] = $c['name_fr']; }
    echo Form::select(['name'=>'country_code','label'=>'Pays','options'=>$opts]);
  ?>

  <div class="grid grid--cards">
    <?= Form::input(['type'=>'number','name'=>'width_mm','label'=>'Largeur (mm)','step'=>'0.01','min'=>'0']); ?>
    <?= Form::input(['type'=>'number','name'=>'height_mm','label'=>'Hauteur (mm)','step'=>'0.01','min'=>'0']); ?>
  </div>

  <?php
    $states = [
      '' => '— État —','Parfaite'=>'Parfaite','Excellente'=>'Excellente','Bonne'=>'Bonne','Moyenne'=>'Moyenne','Endommagé'=>'Endommagé'
    ];
    echo Form::select(['name'=>'current_state','label'=>'État','options'=>$states]);
  ?>

  <div class="grid grid--cards">
    <?= Form::input(['type'=>'number','name'=>'nbr_stamps','label'=>'Nombre d’exemplaires','min'=>'0']); ?>
    <?= Form::input(['name'=>'dimensions','label'=>'Dimensions (texte libre)','placeholder'=>'ex. 40 x 60 mm']); ?>
  </div>

  <?= Form::checkbox(['name'=>'certified','label'=>'Certifié','checked'=>false]); ?>

  <?= Form::actions(['submit'=>'Ajouter','cancel'=> "$base/stamps"]); ?>
  <?= Form::close(); ?>
</section>
