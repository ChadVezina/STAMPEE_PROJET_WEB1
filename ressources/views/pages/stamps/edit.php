<?php

use App\UI\Form;

$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
    <h1>Modifier le timbre: <?= htmlspecialchars($stamp['name']) ?></h1>

    <?= Form::open(['action' => $base . '/stamps/update', 'method' => 'POST', 'enctype' => 'multipart/form-data']); ?>
    <?= \App\Core\CsrfToken::field(); ?>
    <input type="hidden" name="id" value="<?= (int)$stamp['id'] ?>">

    <!-- Basic Information -->
    <fieldset class="form-section">
        <legend>Informations de base</legend>

        <?= Form::input(['name' => 'name', 'label' => 'Nom du timbre', 'required' => true, 'value' => $stamp['name'], 'placeholder' => 'Ex: Timbre Érable Canadien 1965']); ?>

        <?= Form::input(['type' => 'date', 'name' => 'created_at', 'label' => 'Date de création', 'value' => $stamp['created_at'], 'placeholder' => 'YYYY-MM-DD']); ?>

        <?php
        $opts = ['' => "- Sélectionner un pays -"];
        foreach (($countries ?? []) as $c) {
            $opts[$c['iso2']] = $c['name_fr'];
        }
        echo Form::select(['name' => 'country_code', 'label' => 'Pays d\'origine', 'options' => $opts, 'value' => $stamp['country_code']]);
        ?>
    </fieldset>

    <!-- Physical Characteristics -->
    <fieldset class="form-section">
        <legend>Caractéristiques physiques</legend>

        <div class="grid grid--cards">
            <?= Form::input(['type' => 'number', 'name' => 'width_mm', 'label' => 'Largeur (mm)', 'step' => '0.01', 'min' => '0', 'value' => $stamp['width_mm'], 'placeholder' => '24.00']); ?>
            <?= Form::input(['type' => 'number', 'name' => 'height_mm', 'label' => 'Hauteur (mm)', 'step' => '0.01', 'min' => '0', 'value' => $stamp['height_mm'], 'placeholder' => '40.00']); ?>
        </div>

        <?= Form::input(['name' => 'dimensions', 'label' => 'Dimensions (description)', 'value' => $stamp['dimensions'] ?? '', 'placeholder' => 'Ex: 24 x 40 mm, Format rectangulaire']); ?>

        <?php
        $states = [
            '' => '- Sélectionner l\'état -',
            'Parfaite' => 'Parfaite - Aucun défaut visible',
            'Excellente' => 'Excellente - Défauts mineurs',
            'Bonne' => 'Bonne - Quelques défauts visibles',
            'Moyenne' => 'Moyenne - Défauts notables',
            'Endommagée' => 'Endommagée - Défauts importants'
        ];
        echo Form::select(['name' => 'current_state', 'label' => 'État de conservation', 'options' => $states, 'value' => $stamp['current_state'], 'required' => true]);
        ?>

        <?= Form::input(['type' => 'number', 'name' => 'nbr_stamps', 'label' => 'Nombre d\'exemplaires produits', 'min' => '0', 'value' => $stamp['nbr_stamps'], 'placeholder' => 'Ex: 1000000']); ?>

        <?= Form::checkbox(['name' => 'certified', 'label' => 'Timbre certifié par un expert', 'checked' => !empty($stamp['certified'])]); ?>
    </fieldset>

    <!-- Current Images Management -->
    <?php if (!empty($stamp['images'])): ?>
        <fieldset class="form-section">
            <legend>Images actuelles</legend>

            <div class="current-images-grid">
                <?php foreach ($stamp['images'] as $img): ?>
                    <div class="image-item" data-image-id="<?= $img['id'] ?>">
                        <div class="image-container">
                            <img src="<?= htmlspecialchars($img['url']) ?>" alt="Image du timbre">
                            <div class="image-overlay">
                                <?php if (!$img['is_main']): ?>
                                    <button type="button" class="button button--small" onclick="setAsMain(<?= $img['id'] ?>)">
                                        Définir comme principale
                                    </button>
                                <?php else: ?>
                                    <span class="badge">Image principale</span>
                                <?php endif; ?>
                                <button type="button" class="button button--small button--danger" onclick="deleteImage(<?= $img['id'] ?>)">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                        <div class="image-status">
                            <?php if ($img['is_main']): ?>
                                <span class="badge badge-main">Image principale</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Image secondaire</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="help-text">
                <strong>Image principale:</strong> Cette image sera utilisée comme vignette dans les listes et comme première image sur la page de détail.<br>
                <strong>Images secondaires:</strong> Ces images apparaîtront dans la galerie de photos du timbre.
            </p>
        </fieldset>
    <?php endif; ?>

    <!-- Add New Images -->
    <fieldset class="form-section">
        <legend>Ajouter de nouvelles images</legend>

        <div class="image-upload-section">
            <div class="form-group">
                <label for="stamp_images">Sélectionner de nouvelles images</label>
                <input type="file"
                    id="stamp_images"
                    name="stamp_images[]"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    multiple
                    class="form-control">
                <small class="form-text text-muted">
                    • Sélectionnez jusqu'à 5 nouvelles images (JPEG, PNG, GIF, WebP)<br>
                    • Taille maximum: 1MB par image<br>
                    • Les nouvelles images seront ajoutées comme images secondaires
                </small>
            </div>
        </div>
    </fieldset>

    <?= Form::actions(['submit' => 'Mettre à jour le timbre', 'cancel' => "$base/stamps"]); ?>
    <?= Form::close(); ?>
</section>

<!-- CSS styles moved to ressources/scss/pages/_stamp.scss -->

<script>
    const baseUrl = '<?= $base ?>';
    const csrfToken = '<?= \App\Core\CsrfToken::token() ?>';
</script>
<script src="<?= $base ?>/public/assets/js/stamp-edit.js"></script>