<?php

use App\Core\CsrfToken;

$basePath = \App\Core\Config::get('app.base_path', '');
?>

<div class="stamps-create-page">
    <div class="stamps-create-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Ajouter un timbre</h1>
            <p class="page-subtitle">Ajoutez un nouveau timbre à votre collection</p>
        </div>

        <!-- Main Form -->
        <div class="stamps-create-form-section">
            <div class="card">
                <div class="card__header">
                    <h2 class="card__title">
                        <span class="card__icon">🏷️</span>
                        Créer un nouveau timbre
                    </h2>
                    <p class="card__subtitle">Remplissez les informations du timbre à ajouter</p>
                </div>

                <form class="form form--stamps-create" action="<?= htmlspecialchars($basePath) ?>/stamps/store" method="post" enctype="multipart/form-data">
                    <?= CsrfToken::field() ?>

                    <!-- Basic Information -->
                    <fieldset class="form-section">
                        <legend>Informations de base</legend>

                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="name">Nom du timbre *</label>
                                <input class="field__input" type="text" id="name" name="name" required placeholder="Ex: Timbre Érable Canadien 1965" />
                            </div>

                            <div class="field">
                                <label class="field__label" for="created_at">Date de création</label>
                                <input class="field__input" type="date" id="created_at" name="created_at" placeholder="YYYY-MM-DD" />
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__label" for="country_code">Pays d'origine *</label>
                            <select class="field__input" id="country_code" name="country_code" required>
                                <option value="">- Sélectionner un pays -</option>
                                <?php if (!empty($countries)): ?>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= htmlspecialchars($country['iso2'] ?? $country['code'] ?? '') ?>"><?= htmlspecialchars($country['name_fr'] ?? $country['nom'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </fieldset>

                    <!-- Physical Characteristics -->
                    <fieldset class="form-section">
                        <legend>Caractéristiques physiques</legend>

                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="width_mm">Largeur (mm)</label>
                                <input class="field__input" type="number" id="width_mm" name="width_mm" step="0.01" min="0" placeholder="24.00" />
                            </div>

                            <div class="field">
                                <label class="field__label" for="height_mm">Hauteur (mm)</label>
                                <input class="field__input" type="number" id="height_mm" name="height_mm" step="0.01" min="0" placeholder="40.00" />
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__label" for="dimensions">Dimensions (description)</label>
                            <input class="field__input" type="text" id="dimensions" name="dimensions" placeholder="Ex: 24 x 40 mm, Format rectangulaire" />
                        </div>

                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="current_state">État de conservation *</label>
                                <select class="field__input" id="current_state" name="current_state" required>
                                    <option value="">- Sélectionner l'état -</option>
                                    <option value="Parfaite">Parfaite - Aucun défaut visible</option>
                                    <option value="Excellente">Excellente - Défauts mineurs</option>
                                    <option value="Bonne">Bonne - Quelques défauts visibles</option>
                                    <option value="Moyenne">Moyenne - Défauts notables</option>
                                    <option value="Endommagée">Endommagée - Défauts importants</option>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label" for="nbr_stamps">Nombre d'exemplaires produits</label>
                                <input class="field__input" type="number" id="nbr_stamps" name="nbr_stamps" min="0" placeholder="Ex: 1000000" />
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__checkbox">
                                <input type="checkbox" id="certified" name="certified" value="1" />
                                <span class="field__checkbox-mark"></span>
                                Timbre certifié par un expert
                            </label>
                        </div>
                    </fieldset>

                    <!-- Images Section -->
                    <fieldset class="form-section">
                        <legend>Images du timbre</legend>

                        <div class="image-upload-section">
                            <div class="field">
                                <label class="field__label" for="stamp_images">Ajouter des images <span id="image-counter" class="text-muted">(0/5)</span></label>
                                <input class="field__input" type="file" id="stamp_images" name="stamp_images[]" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/tiff,image/svg+xml,image/x-icon,image/avif,.jfif" multiple />
                                <p class="field__help">
                                    • Sélectionnez jusqu'à 5 images (JPEG, PNG, GIF, WebP, BMP, TIFF, SVG, JFIF, AVIF, ICO)<br>
                                    • Taille maximum: 5MB par image<br>
                                    • La première image sera automatiquement définie comme image principale<br>
                                    • Vous pouvez ajouter plusieurs images en une fois ou une par une
                                </p>
                            </div>

                            <div id="image-preview" class="image-preview-grid" style="display: none;">
                                <h4>Aperçu des images</h4>
                                <div class="preview-container"></div>
                                <small class="text-muted">Vous pourrez réorganiser les images après la création du timbre.</small>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Auction Settings (Required) -->
                    <fieldset class="form-section">
                        <legend>Mise aux enchères</legend>
                        <p class="field__help">Ce timbre sera mis aux enchères — remplissez les champs obligatoires ci-dessous.</p>

                        <!-- Hidden input to always create auction -->
                        <input type="hidden" name="create_auction" value="1" />

                        <div id="auction-fields">
                            <div class="form__row">
                                <div class="field">
                                    <label class="field__label" for="auction_start">Début de l'enchère *</label>
                                    <input class="field__input" type="datetime-local" id="auction_start" name="auction_start" required />
                                </div>

                                <div class="field">
                                    <label class="field__label" for="auction_end">Fin de l'enchère *</label>
                                    <input class="field__input" type="datetime-local" id="auction_end" name="auction_end" required />
                                </div>
                            </div>

                            <div class="form__row">
                                <div class="field">
                                    <label class="field__label" for="min_price">Prix de départ (CAD) *</label>
                                    <input class="field__input" type="number" id="min_price" name="min_price" step="0.01" min="0.01" placeholder="25.00" required />
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form__actions">
                        <button type="submit" class="button button--primary">
                            <span class="button__icon">✅</span>
                            Créer le timbre
                        </button>
                        <a href="<?= htmlspecialchars($basePath) ?>/stamps" class="button button--secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- External styles and scripts for stamps create form -->
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/public/assets/css/form-components.css" />
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/public/assets/css/stamps-create.css" />
<script src="<?= htmlspecialchars($basePath) ?>/public/assets/js/form-utils.js" defer></script>
<script src="<?= htmlspecialchars($basePath) ?>/public/assets/js/stamps-create.js" defer></script>