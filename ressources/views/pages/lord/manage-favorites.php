<?php

/** @var array $stamps */
/** @var array $favoriteStampIds */
/** @var string|null $success */
/** @var string|null $error */
$base = \App\Core\Config::get('app.base_url');
?>

<section class="lord-favorites">
    <header class="lord-favorites__header">
        <div class="lord-favorites__title-area">
            <h1 class="lord-favorites__title">👑 Gestion des Coups de Cœur du Lord</h1>
            <p class="lord-favorites__subtitle">
                Sélectionnez les timbres qui méritent d'être mis en avant sur la page d'accueil
            </p>
        </div>

        <div class="lord-favorites__actions">
            <a href="<?= $base ?>/lord/logout" class="button button--secondary">
                🚪 Déconnexion
            </a>
        </div>
    </header>

    <?php if ($success): ?>
        <div class="alert alert--success">
            <strong>Succès:</strong> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert--error">
            <strong>Erreur:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="lord-favorites__stats">
        <div class="stat-card">
            <span class="stat-card__value"><?= count($favoriteStampIds) ?></span>
            <span class="stat-card__label">Coups de Cœur actifs</span>
        </div>
        <div class="stat-card">
            <span class="stat-card__value"><?= count($stamps) ?></span>
            <span class="stat-card__label">Timbres disponibles</span>
        </div>
    </div>

    <div class="lord-favorites__content">
        <div class="stamps-grid">
            <?php if (empty($stamps)): ?>
                <div class="no-stamps">
                    <p>Aucun timbre disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($stamps as $stamp): ?>
                    <?php $isFavorite = in_array($stamp['id'], $favoriteStampIds); ?>
                    <div class="stamp-card <?= $isFavorite ? 'stamp-card--favorite' : '' ?>">
                        <div class="stamp-card__image">
                            <?php if (!empty($stamp['main_image'])): ?>
                                <img src="<?= htmlspecialchars($base . $stamp['main_image']) ?>"
                                    alt="<?= htmlspecialchars($stamp['name']) ?>"
                                    onerror="this.src='<?= $base ?>/assets/img/placeholder-stamp.png'">
                            <?php else: ?>
                                <div class="stamp-card__placeholder">
                                    📮
                                </div>
                            <?php endif; ?>

                            <?php if ($isFavorite): ?>
                                <div class="stamp-card__badge">
                                    ⭐ Coup de Cœur
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="stamp-card__content">
                            <h3 class="stamp-card__name"><?= htmlspecialchars($stamp['name']) ?></h3>

                            <div class="stamp-card__meta">
                                <?php if (!empty($stamp['country_name'])): ?>
                                    <span class="stamp-card__country">
                                        🌍 <?= htmlspecialchars($stamp['country_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($stamp['owner_name'])): ?>
                                    <span class="stamp-card__owner">
                                        👤 <?= htmlspecialchars($stamp['owner_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <form method="POST" action="<?= $base ?>/lord/favorites/toggle" class="stamp-card__action">
                                <input type="hidden" name="stamp_id" value="<?= (int)$stamp['id'] ?>">

                                <?php if ($isFavorite): ?>
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="button button--danger button--small">
                                        ❌ Retirer des Coups de Cœur
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="button button--primary button--small">
                                        ⭐ Ajouter aux Coups de Cœur
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Styles moved to ressources/scss/pages/_lord-favorites.scss -->