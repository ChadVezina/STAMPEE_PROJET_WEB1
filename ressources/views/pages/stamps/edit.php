<?php

use App\UI\Form;

$base = \App\Core\Config::get('app.base_url');
?>
<section class="card-form">
    <h1>Modifier le timbre</h1>

    <?= Form::open(['action' => $base . '/stamps/update', 'method' => 'POST', 'enctype' => 'multipart/form-data']); ?>
    <?= \App\Core\CsrfToken::field(); ?>
    <input type="hidden" name="id" value="<?= (int)$stamp['id'] ?>">

    <?= Form::input(['name' => 'name', 'label' => 'Nom', 'required' => true, 'value' => $stamp['name'], 'placeholder' => 'Nom du timbre']); ?>

    <?= Form::input(['type' => 'date', 'name' => 'created_at', 'label' => 'Date de création (si connue)', 'value' => $stamp['created_at'], 'placeholder' => 'YYYY-MM-DD']); ?>

    <?php
    $opts = ['' => '— Pays d\'origine —'];
    foreach ($countries ?? [] as $c) {
        $opts[$c['iso2']] = $c['name_fr'];
    }
    echo Form::select(['name' => 'country_code', 'label' => 'Pays', 'options' => $opts, 'value' => $stamp['country_code']]);
    ?>

    <div class="grid grid--cards">
        <?= Form::input(['type' => 'number', 'name' => 'width_mm', 'label' => 'Largeur (mm)', 'step' => '0.01', 'min' => '0', 'value' => $stamp['width_mm']]); ?>
        <?= Form::input(['type' => 'number', 'name' => 'height_mm', 'label' => 'Hauteur (mm)', 'step' => '0.01', 'min' => '0', 'value' => $stamp['height_mm']]); ?>
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
    echo Form::select(['name' => 'current_state', 'label' => 'État', 'options' => $states, 'value' => $stamp['current_state']]);
    ?>

    <div class="grid grid--cards">
        <?= Form::input(['type' => 'number', 'name' => 'nbr_stamps', 'label' => 'Nombre d\'exemplaires', 'min' => '0', 'value' => $stamp['nbr_stamps']]); ?>
        <?= Form::input(['name' => 'dimensions', 'label' => 'Dimensions (texte libre)', 'placeholder' => 'ex. 40 x 60 mm', 'value' => $stamp['dimensions']]); ?>
    </div>

    <?= Form::checkbox(['name' => 'certified', 'label' => 'Certifié', 'checked' => !empty($stamp['certified'])]); ?>

    <!-- Current Images -->
    <?php if (!empty($stamp['images'])): ?>
        <div class="form-group">
            <label>Images actuelles</label>
            <div class="current-images">
                <?php foreach ($stamp['images'] as $img): ?>
                    <div class="current-image">
                        <img src="<?= htmlspecialchars($img['url']) ?>" alt="Image du timbre">
                        <div class="current-image__controls">
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
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Add New Images -->
    <div class="form-group">
        <label for="stamp_images">Ajouter de nouvelles images</label>
        <input type="file"
            id="stamp_images"
            name="stamp_images[]"
            accept="image/jpeg,image/png,image/gif,image/webp"
            multiple
            class="form-control">
        <small class="form-text text-muted">
            Vous pouvez ajouter de nouvelles images. Formats acceptés: JPEG, PNG, GIF, WebP
        </small>
    </div>

    <?= Form::actions(['submit' => 'Mettre à jour', 'cancel' => "$base/stamps"]); ?>
    <?= Form::close(); ?>
</section>

<!-- CSS styles moved to ressources/scss/pages/_stamp.scss -->

<script>
    const baseUrl = '<?= $base ?>';
    const csrfToken = '<?= \App\Core\CsrfToken::token() ?>';

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

    function setAsMain(imageId) {
        if (confirm('Définir cette image comme image principale ?')) {
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('_token', csrfToken);

            fetch(baseUrl + '/stamps/image/set-main', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Image principale mise à jour avec succès !');
                        location.reload(); // Refresh to show changes
                    } else {
                        alert('Erreur: ' + (data.message || 'Impossible de définir comme image principale'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur de communication avec le serveur');
                });
        }
    }

    function deleteImage(imageId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('_token', csrfToken);

            fetch(baseUrl + '/stamps/image/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Image supprimée avec succès !');
                        location.reload(); // Refresh to show changes
                    } else {
                        alert('Erreur: ' + (data.message || 'Impossible de supprimer l\'image'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur de communication avec le serveur');
                });
        }
    }
</script>