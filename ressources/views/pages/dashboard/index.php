<?php

use App\Core\CsrfToken;

$u = $user ?? $_SESSION['user'] ?? null;
$mode ??= 'default';
$basePath = \App\Core\Config::get('app.base_path', '');
?>

<div class="dashboard-page">
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">Tableau de bord</h1>
            <p class="dashboard-welcome">Bienvenue, <strong><?= htmlspecialchars($u['nom'] ?? 'Utilisateur'); ?></strong>.</p>
        </div>

        <?php if ($mode === 'default'): ?>
            <!-- Default Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-content">
                            <h3>Vos enchères</h3>
                            <p>Gérez vos enchères actives</p>
                            <a href="<?= htmlspecialchars($basePath) ?>/auctions" class="button button--secondary">Voir les enchères</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🏷️</div>
                        <div class="stat-content">
                            <h3>Vos timbres</h3>
                            <p>Consultez votre collection</p>
                            <a href="<?= htmlspecialchars($basePath) ?>/stamps" class="button button--secondary">Voir les timbres</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">⚙️</div>
                        <div class="stat-content">
                            <h3>Paramètres</h3>
                            <p>Gérez votre compte et vos préférences</p>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=profile" class="button button--primary">Paramètres</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($mode === 'add-stamp'): ?>
            <!-- Add Stamp Form -->
            <div class="dashboard-form-section">
                <div class="card">
                    <div class="card__header">
                        <h2 class="card__title">
                            <span class="card__icon">🏷️</span>
                            Ajouter un timbre
                        </h2>
                        <p class="card__subtitle">Ajoutez un nouveau timbre à votre collection</p>
                    </div>

                    <form class="form form--dashboard" action="<?= htmlspecialchars($basePath) ?>/dashboard/add-stamp" method="post" enctype="multipart/form-data">
                        <?= CsrfToken::field() ?>

                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="name">Nom du timbre *</label>
                                <input class="field__input" type="text" id="name" name="name" required placeholder="Ex: Timbre commémoratif de 1985" />
                            </div>

                            <div class="field">
                                <label class="field__label" for="year">Année d'émission *</label>
                                <input class="field__input" type="number" id="year" name="year" min="1840" max="<?= date('Y') ?>" required placeholder="Ex: 1985" />
                            </div>
                        </div>

                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="color">Couleur principale *</label>
                                <input class="field__input" type="text" id="color" name="color" required placeholder="Ex: Rouge, Bleu, Multicolore" />
                            </div>

                            <div class="field">
                                <label class="field__label" for="country_id">Pays d'origine *</label>
                                <select class="field__input" id="country_id" name="country_id" required>
                                    <option value="">Sélectionnez un pays</option>
                                    <?php if (!empty($countries)): ?>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= (int)$country['id'] ?>"><?= htmlspecialchars($country['nom']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__label" for="description">Description</label>
                            <textarea class="field__input" id="description" name="description" rows="4" placeholder="Description détaillée du timbre..."></textarea>
                        </div>

                        <div class="field">
                            <label class="field__label" for="images">Images du timbre</label>
                            <input class="field__input" type="file" id="images" name="images[]" multiple accept="image/*" />
                            <p class="field__help">Vous pouvez sélectionner plusieurs images (formats acceptés: JPG, PNG, GIF)</p>
                        </div>

                        <div class="form__actions">
                            <button type="submit" class="button button--primary">
                                <span class="button__icon">✅</span>
                                Ajouter le timbre
                            </button>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="button button--secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($mode === 'profile'): ?>
            <!-- Profile Options -->
            <div class="dashboard-profile-section">
                <div class="profile-header">
                    <h2 class="profile-title">⚙️ Paramètres du compte</h2>
                    <p class="profile-subtitle">Gérez les paramètres de votre compte et vos préférences</p>
                </div>

                <div class="profile-options">
                    <div class="profile-option-card">
                        <div class="option-icon">🔒</div>
                        <h3>Changer le mot de passe</h3>
                        <p>Modifiez votre mot de passe de connexion pour renforcer la sécurité</p>
                        <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=change-password" class="button button--primary">Modifier</a>
                    </div>

                    <div class="profile-option-card">
                        <div class="option-icon">📧</div>
                        <h3>Changer l'adresse e-mail</h3>
                        <p>Modifiez l'adresse e-mail associée à votre compte</p>
                        <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=change-email" class="button button--primary">Modifier</a>
                    </div>

                    <div class="profile-option-card option-card--danger">
                        <div class="option-icon">🗑️</div>
                        <h3>Supprimer le compte</h3>
                        <p>Supprimez définitivement votre compte et toutes vos données</p>
                        <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=delete-account" class="button button--danger">Supprimer</a>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="button button--secondary">
                        <span class="button__icon">←</span>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>

        <?php elseif ($mode === 'change-password'): ?>
            <!-- Change Password Form -->
            <div class="dashboard-form-section">
                <div class="card">
                    <div class="card__header">
                        <h2 class="card__title">
                            <span class="card__icon">🔒</span>
                            Changer le mot de passe
                        </h2>
                        <p class="card__subtitle">Modifiez votre mot de passe de connexion</p>
                    </div>

                    <form class="form form--dashboard" action="<?= htmlspecialchars($basePath) ?>/dashboard/change-password" method="post">
                        <?= CsrfToken::field() ?>

                        <div class="field field--password">
                            <label class="field__label" for="current_password">Mot de passe actuel *</label>
                            <div class="field__input-wrapper">
                                <input class="field__input" type="password" id="current_password" name="current_password" required />
                                <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
                                    <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="field field--password">
                            <label class="field__label" for="new_password">Nouveau mot de passe *</label>
                            <div class="field__input-wrapper">
                                <input class="field__input" type="password" id="new_password" name="new_password" required minlength="8" />
                                <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
                                    <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <p class="field__help">Minimum 8 caractères</p>
                        </div>

                        <div class="field field--password">
                            <label class="field__label" for="confirm_password">Confirmer le nouveau mot de passe *</label>
                            <div class="field__input-wrapper">
                                <input class="field__input" type="password" id="confirm_password" name="confirm_password" required minlength="8" />
                                <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
                                    <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form__actions">
                            <button type="submit" class="button button--primary">
                                <span class="button__icon">✅</span>
                                Modifier le mot de passe
                            </button>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=profile" class="button button--secondary">← Retour aux paramètres</a>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="button button--tertiary">Tableau de bord</a>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($mode === 'change-email'): ?>
            <!-- Change Email Form -->
            <div class="dashboard-form-section">
                <div class="card">
                    <div class="card__header">
                        <h2 class="card__title">
                            <span class="card__icon">📧</span>
                            Changer l'adresse e-mail
                        </h2>
                        <p class="card__subtitle">Votre adresse actuelle: <strong><?= htmlspecialchars($u['email']) ?></strong></p>
                    </div>

                    <form class="form form--dashboard" action="<?= htmlspecialchars($basePath) ?>/dashboard/change-email" method="post">
                        <?= CsrfToken::field() ?>

                        <div class="field">
                            <label class="field__label" for="new_email">Nouvelle adresse e-mail *</label>
                            <input class="field__input" type="email" id="new_email" name="new_email" required />
                        </div>

                        <div class="field field--password">
                            <label class="field__label" for="password">Mot de passe actuel *</label>
                            <div class="field__input-wrapper">
                                <input class="field__input" type="password" id="password" name="password" required />
                                <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
                                    <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <p class="field__help">Confirmez votre mot de passe pour valider la modification</p>
                        </div>

                        <div class="form__actions">
                            <button type="submit" class="button button--primary">
                                <span class="button__icon">✅</span>
                                Modifier l'adresse e-mail
                            </button>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=profile" class="button button--secondary">← Retour aux paramètres</a>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="button button--tertiary">Tableau de bord</a>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($mode === 'delete-account'): ?>
            <!-- Delete Account Form -->
            <div class="dashboard-form-section">
                <div class="card card--danger">
                    <div class="card__header">
                        <h2 class="card__title">
                            <span class="card__icon">⚠️</span>
                            Supprimer le compte
                        </h2>
                        <p class="card__subtitle">Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
                    </div>

                    <form class="form form--dashboard" action="<?= htmlspecialchars($basePath) ?>/dashboard/delete-account" method="post">
                        <?= CsrfToken::field() ?>

                        <div class="field field--password">
                            <label class="field__label" for="password">Mot de passe *</label>
                            <div class="field__input-wrapper">
                                <input class="field__input" type="password" id="password" name="password" required />
                                <button type="button" class="field__toggle" aria-label="Afficher/masquer le mot de passe">
                                    <svg class="field__toggle-icon field__toggle-icon--hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="field__toggle-icon field__toggle-icon--show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__label" for="confirmation">Tapez "SUPPRIMER" pour confirmer *</label>
                            <input class="field__input" type="text" id="confirmation" name="confirmation" required placeholder="SUPPRIMER" />
                            <p class="field__help">Tapez exactement "SUPPRIMER" (en majuscules) pour confirmer la suppression</p>
                        </div>

                        <div class="form__actions">
                            <button type="submit" class="button button--danger">
                                <span class="button__icon">🗑️</span>
                                Supprimer définitivement le compte
                            </button>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=profile" class="button button--secondary">← Retour aux paramètres</a>
                            <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="button button--tertiary">Tableau de bord</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>