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

<style>
    .lord-favorites {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .lord-favorites__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .lord-favorites__title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .lord-favorites__subtitle {
        color: #718096;
        margin: 0.5rem 0 0 0;
        font-size: 1.125rem;
    }

    .lord-favorites__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
    }

    .stat-card__value {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
    }

    .stat-card__label {
        color: #718096;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stamps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .stamp-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .stamp-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stamp-card--favorite {
        border-color: #fbbf24;
        background: #fffbeb;
    }

    .stamp-card__image {
        position: relative;
        height: 200px;
        background: #f7fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .stamp-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stamp-card__placeholder {
        font-size: 3rem;
        color: #cbd5e0;
    }

    .stamp-card__badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: #fbbf24;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stamp-card__content {
        padding: 1.5rem;
    }

    .stamp-card__name {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 1rem 0;
        line-height: 1.3;
    }

    .stamp-card__meta {
        margin-bottom: 1.5rem;
    }

    .stamp-card__country,
    .stamp-card__owner {
        display: block;
        color: #718096;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .stamp-card__action {
        margin: 0;
    }

    .button--small {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        width: 100%;
    }

    .button--danger {
        background-color: #e53e3e;
        border-color: #e53e3e;
        color: white;
    }

    .button--danger:hover {
        background-color: #c53030;
        border-color: #c53030;
    }

    .no-stamps {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem;
        color: #718096;
        font-size: 1.125rem;
    }

    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }

    .alert--success {
        background-color: #c6f6d5;
        color: #22543d;
        border: 1px solid #68d391;
    }

    .alert--error {
        background-color: #fed7d7;
        color: #c53030;
        border: 1px solid #fc8181;
    }

    @media (max-width: 768px) {
        .lord-favorites__header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .lord-favorites__title {
            font-size: 2rem;
        }

        .stamps-grid {
            grid-template-columns: 1fr;
        }
    }
</style>