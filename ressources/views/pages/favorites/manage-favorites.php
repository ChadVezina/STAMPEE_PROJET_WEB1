<?php

/** @var array $auctions */
/** @var array $favoriteAuctionIds */
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
            <span class="stat-card__value"><?= count($favoriteAuctionIds) ?></span>
            <span class="stat-card__label">Coups de Cœur actifs</span>
        </div>
        <div class="stat-card">
            <span class="stat-card__value"><?= count($auctions) ?></span>
            <span class="stat-card__label">Enchères disponibles</span>
        </div>
    </div>

    <div class="lord-favorites__content">
        <div class="auctions-grid">
            <?php if (empty($auctions)): ?>
                <div class="no-auctions">
                    <p>Aucune enchère disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($auctions as $auction): ?>
                    <?php $isFavorite = in_array($auction['auction_id'], $favoriteAuctionIds); ?>
                    <article class="card card--auction <?= $isFavorite ? 'card--favorite' : '' ?>">
                        <div class="card__image" style="background-image:url('<?= htmlspecialchars($auction['main_image'] ?? '', ENT_QUOTES) ?>');">
                            <?php if ($isFavorite): ?>
                                <div class="card__badge card__badge--favorite">⭐ Coup de Cœur</div>
                            <?php endif; ?>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title">
                                <?= htmlspecialchars($auction['stamp_name']) ?>
                                <?php if ($isFavorite): ?>
                                    <span class="status-badge status-badge--favorite">❤️ Favori</span>
                                <?php endif; ?>
                            </h3>
                            <p class="card__price">
                                Prix: <?= (isset($auction['current_price']) && $auction['current_price'] > 0)
                                            ? number_format((float)$auction['current_price'], 2) . ' $ CAD'
                                            : number_format((float)$auction['min_price'], 2) . ' $ CAD' ?>
                                <?php if (isset($auction['current_price']) && $auction['current_price'] > 0): ?>
                                    <span class="price-label">(avec enchères)</span>
                                <?php else: ?>
                                    <span class="price-label">(prix minimum)</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($auction['auction_end'])): ?>
                                <div class="card__time-remaining" data-end-time="<?= date('c', strtotime($auction['auction_end'])) ?>">
                                    <span class="card__countdown">Calcul en cours...</span>
                                </div>
                            <?php endif; ?>
                            <p class="card__meta">
                                Pays: <?= htmlspecialchars($auction['country_name'] ?? 'N/A') ?>
                            </p>
                            <p class="card__owner">
                                Vendeur: <?= htmlspecialchars($auction['seller_name'] ?? 'N/A') ?>
                            </p>
                            <form method="post" action="<?= $base ?>/lord/favorites/toggle" class="favorite-form">
                                <input type="hidden" name="auction_id" value="<?= $auction['auction_id'] ?>">
                                <input type="hidden" name="action" value="<?= $isFavorite ? 'remove' : 'add' ?>">
                                <button type="submit" class="favorite-heart <?= $isFavorite ? 'favorite-heart--filled' : 'favorite-heart--empty' ?>"
                                    title="<?= $isFavorite ? 'Retirer des Coups de Cœur' : 'Ajouter aux Coups de Cœur du Lord' ?>">
                                    <span class="heart-icon">
                                        <?= $isFavorite ? '♥' : '🤍' ?>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Include countdown functionality -->
<?php include_once __DIR__ . '/../../partials/countdown-timer.php'; ?>

<script>
    // Initialize countdowns when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.card__countdown')) {
            initializeCountdowns();
        }
    });
</script>