<?php

use App\UI\Form;

$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
  <h1>Ajouter un timbre</h1>

  <?= Form::open(['action' => "$base/stamps/store", 'method' => 'POST', 'enctype' => 'multipart/form-data']); ?>
  <?= \App\Core\CsrfToken::field(); ?>

  <?= Form::input(['name' => 'name', 'label' => 'Nom', 'required' => true, 'placeholder' => 'Nom du timbre']); ?>

  <?= Form::input(['type' => 'date', 'name' => 'created_at', 'label' => 'Date de création (si connue)', 'placeholder' => 'YYYY-MM-DD']); ?>

  <?php
  $opts = ['' => '— Pays d’origine —'];
  foreach (($countries ?? []) as $c) {
    $opts[$c['iso2']] = $c['name_fr'];
  }
  echo Form::select(['name' => 'country_code', 'label' => 'Pays', 'options' => $opts]);
  ?>

  <div class="grid grid--cards">
    <?= Form::input(['type' => 'number', 'name' => 'width_mm', 'label' => 'Largeur (mm)', 'step' => '0.01', 'min' => '0']); ?>
    <?= Form::input(['type' => 'number', 'name' => 'height_mm', 'label' => 'Hauteur (mm)', 'step' => '0.01', 'min' => '0']); ?>
  </div>

  <?php
  $states = [
    '' => '— État —',
    'Parfaite' => 'Parfaite',
    'Excellente' => 'Excellente',
    'Bonne' => 'Bonne',
    'Moyenne' => 'Moyenne',
    'Endommagée' => 'Endommagée'
  ];
  echo Form::select(['name' => 'current_state', 'label' => 'État', 'options' => $states]);
  ?>

  <div class="grid grid--cards">
    <?= Form::input(['type' => 'number', 'name' => 'nbr_stamps', 'label' => 'Nombre d’exemplaires', 'min' => '0']); ?>
    <?= Form::input(['name' => 'dimensions', 'label' => 'Dimensions (texte libre)', 'placeholder' => 'ex. 40 x 60 mm']); ?>
  </div>

  <?= Form::checkbox(['name' => 'certified', 'label' => 'Certifié', 'checked' => false]); ?>

  <!-- Image Upload Section -->
  <div class="form-group">
    <label for="stamp_images">Images du timbre</label>
    <input type="file"
      id="stamp_images"
      name="stamp_images[]"
      accept="image/jpeg,image/png,image/gif,image/webp"
      multiple
      class="form-control">
    <small class="form-text text-muted">
      Vous pouvez sélectionner plusieurs images. La première image sera utilisée comme image principale.
      Formats acceptés: JPEG, PNG, GIF, WebP
    </small>
  </div>

  <?= Form::actions(['submit' => 'Ajouter', 'cancel' => "$base/stamps"]); ?>
  <?= Form::close(); ?>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('stamp_images');

    imageInput.addEventListener('change', function() {
      const files = this.files;
      const maxFiles = 5;
      const maxSize = 5 * 1024 * 1024; // 5MB

      if (files.length > maxFiles) {
        alert(`Vous ne pouvez sélectionner que ${maxFiles} images maximum.`);
        this.value = '';
        return;
      }

      for (let i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
          alert(`L'image "${files[i].name}" est trop volumineuse (max 5MB).`);
          this.value = '';
          return;
        }
      }
    });
  });
</script>