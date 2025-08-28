<?php
// DEAD CODE - This file is no longer used
// Favorites are now displayed on the home page via FavoriteService->listFeatured()
// and managed through /lord/favorites/manage
// This file can be safely deleted
die('This page has been deprecated. Favorites are now shown on the home page.');
?>

<section class="favorites">
    <header class="favorites__header">
        <h1 class="favorites__title">
            <span class="lord-crown">👑</span>
            Les Coups de Cœur du Lord
        </h1>
        <p class="favorites__subtitle">
            Découvrez notre sélection exclusive de timbres d'exception, personnellement choisis par notre expert.
        </p>
    </header>

    <?php if (empty($favorites)): ?>
        <div class="favorites__empty">
            <div class="empty-state">
                <span class="empty-state__icon">💎</span>
                <h3 class="empty-state__title">Aucun coup de cœur pour le moment</h3>
                <p class="empty-state__message">
                    Le Lord n'a pas encore sélectionné ses coups de cœur. Revenez bientôt pour découvrir sa sélection exclusive !
                </p>
                <a href="<?= htmlspecialchars($base) ?>/auctions" class="button button--primary">
                    Voir toutes les enchères
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="favorites__grid">
            <?php foreach ($favorites as $auction): ?>
                <article class="favorite-card">
                    <div class="favorite-card__badge">
                        <span class="favorite-badge">
                            <span class="favorite-badge__icon">👑</span>
                            <span class="favorite-badge__text">Coup de Cœur</span>
                        </span>
                    </div>

                    <div class="favorite-card__image">
                        <?php if (!empty($auction['main_image'])): ?>
                            <img src="<?= htmlspecialchars($base . $auction['main_image']) ?>"
                                alt="<?= htmlspecialchars($auction['stamp_name']) ?>"
                                loading="lazy">
                        <?php else: ?>
                            <div class="no-image">
                                <span class="no-image__icon">📮</span>
                                <span class="no-image__text">Aucune image</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="favorite-card__content">
                        <h3 class="favorite-card__title">
                            <?= htmlspecialchars($auction['stamp_name']) ?>
                        </h3>

                        <div class="favorite-card__seller">
                            Vendu par <strong><?= htmlspecialchars($auction['seller_name']) ?></strong>
                        </div>

                        <div class="favorite-card__price">
                            <?php
                            $currentPrice = $auction['current_price'] ?? $auction['min_price'];
                            echo number_format($currentPrice, 2) . ' $ CAD';
                            ?>
                        </div>

                        <div class="favorite-card__timing">
                            <?php
                            $endTime = new DateTime($auction['auction_end']);
                            $now = new DateTime();
                            if ($endTime > $now): ?>
                                <div class="auction-card__countdown" data-end-time="<?= $auction['auction_end'] ?>">
                                    <span class="countdown-text">Calcul en cours...</span>
                                </div>
                            <?php else: ?>
                                <div class="auction-status auction-status--ended">
                                    Enchère terminée
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="favorite-card__actions">
                            <a href="<?= htmlspecialchars($base) ?>/auctions/<?= $auction['id'] ?>"
                                class="button button--primary">
                                Voir l'enchère
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <footer class="favorites__footer">
        <div class="lord-signature">
            <p>
                <span class="lord-crown">👑</span>
                <em>"Une sélection raffinée pour les vrais connaisseurs"</em>
            </p>
            <p class="signature-text">— Le Lord de Stampee</p>
        </div>
    </footer>
</section>

<script>
    // Countdown Timer Functionality for Favorite Cards
    function updateCountdowns() {
        const countdownElements = document.querySelectorAll('.auction-card__countdown');

        countdownElements.forEach(element => {
            const endTime = new Date(element.getAttribute('data-end-time')).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                element.innerHTML = '<span class="countdown-text">Terminée</span>';
                element.classList.add('expired');
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) {
                timeString = `${days}j ${hours}h ${minutes}m`;
            } else if (hours > 0) {
                timeString = `${hours}h ${minutes}m ${seconds}s`;
            } else if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
            } else {
                timeString = `${seconds}s`;
            }

            element.innerHTML = `<span class="countdown-text">${timeString}</span>`;
        });
    }

    // Start countdown if there are auction cards
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.auction-card__countdown')) {
            updateCountdowns(); // Initial update
            setInterval(updateCountdowns, 1000); // Update every second
        }
    });
</script>