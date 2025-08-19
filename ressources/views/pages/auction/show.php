<?php

/** @var array $auction */
/** @var array $bids */
$base = \App\Core\Config::get('app.base_url');

// Format auction dates
$startDate = new DateTime($auction['auction_start']);
$endDate = new DateTime($auction['auction_end']);
$now = new DateTime();

$isActive = $now >= $startDate && $now <= $endDate;
$hasEnded = $now > $endDate;
$hasStarted = $now >= $startDate;

// Get main image
$mainImage = '';
if (!empty($auction['main_image'])) {
    $mainImage = $auction['main_image'];
}
?>

<div class="auction-detail">
    <div class="auction-header">
        <div class="breadcrumb">
            <a href="<?= $base ?>/auctions" class="breadcrumb-link">Enchères</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($auction['stamp_name']) ?></span>
        </div>

        <div class="auction-status">
            <?php if ($hasEnded): ?>
                <span class="status-badge status-badge--ended">Terminée</span>
            <?php elseif ($isActive): ?>
                <span class="status-badge status-badge--active">En cours</span>
            <?php else: ?>
                <span class="status-badge status-badge--upcoming">À venir</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="auction-content">
        <div class="auction-main">
            <div class="stamp-preview">
                <?php if ($mainImage): ?>
                    <img src="<?= htmlspecialchars($mainImage) ?>"
                        alt="<?= htmlspecialchars($auction['stamp_name']) ?>"
                        class="stamp-image">
                <?php else: ?>
                    <div class="stamp-placeholder">
                        <div class="placeholder-icon">🏷️</div>
                        <p>Aucune image disponible</p>
                    </div>
                <?php endif; ?>

                <div class="stamp-actions">
                    <a href="<?= $base ?>/stamps/show?id=<?= $auction['stamp_id'] ?>"
                        class="button button--secondary button--full">
                        <span class="button-icon">👁️</span>
                        Voir le timbre en détail
                    </a>
                </div>
            </div>

            <div class="auction-info">
                <h1 class="auction-title"><?= htmlspecialchars($auction['stamp_name']) ?></h1>

                <div class="auction-meta">
                    <div class="meta-item">
                        <strong>Vendeur:</strong>
                        <?= htmlspecialchars($auction['seller_name'] ?? 'Non spécifié') ?>
                    </div>

                    <div class="meta-item">
                        <strong>Prix minimum:</strong>
                        <span class="price"><?= number_format($auction['min_price'], 2) ?> €</span>
                    </div>

                    <div class="meta-item">
                        <strong>Début:</strong>
                        <?= $startDate->format('d/m/Y à H:i') ?>
                    </div>

                    <div class="meta-item">
                        <strong>Fin:</strong>
                        <?= $endDate->format('d/m/Y à H:i') ?>
                    </div>
                </div>

                <?php if ($isActive): ?>
                    <div class="auction-countdown">
                        <h3>Temps restant</h3>
                        <div class="countdown-display" data-end-time="<?= date('c', strtotime($auction['auction_end'])) ?>">
                            <span class="countdown-timer">Calcul en cours...</span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($isActive): ?>
                    <div class="bidding-section">
                        <h3>Faire une enchère</h3>
                        <form action="<?= $base ?>/bid/store" method="post" class="bid-form">
                            <input type="hidden" name="_token" value="<?= \App\Core\CsrfToken::token() ?>">
                            <input type="hidden" name="auction_id" value="<?= $auction['id'] ?>">

                            <div class="form-group">
                                <label for="amount" class="form-label">Montant (€)</label>
                                <input type="number"
                                    id="amount"
                                    name="amount"
                                    class="form-input"
                                    min="<?= $auction['min_price'] ?>"
                                    step="0.01"
                                    required>
                            </div>

                            <button type="submit" class="button button--primary button--full">
                                Enchérir
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="auction-sidebar">
            <div class="bids-section">
                <h3>Historique des enchères</h3>

                <?php if (empty($bids)): ?>
                    <p class="no-bids">Aucune enchère pour le moment.</p>
                <?php else: ?>
                    <div class="bids-list">
                        <?php foreach ($bids as $bid): ?>
                            <div class="bid-item">
                                <div class="bid-amount"><?= number_format($bid['amount'], 2) ?> €</div>
                                <div class="bid-bidder"><?= htmlspecialchars($bid['bidder_name'] ?? 'Anonyme') ?></div>
                                <div class="bid-time"><?= date('d/m à H:i', strtotime($bid['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- CSS styles moved to ressources/scss/pages/_auction.scss and ressources/scss/components/_countdown.scss -->

<script>
    // Countdown Timer Functionality for Auction Detail
    function updateCountdown() {
        const countdownElement = document.querySelector('.countdown-timer');
        if (!countdownElement) return;

        const countdownDisplay = countdownElement.closest('.countdown-display');
        const endTime = new Date(countdownDisplay.getAttribute('data-end-time')).getTime();
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            countdownElement.innerHTML = "Enchère terminée";
            countdownElement.classList.add('urgent');
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let timeString = '';
        if (days > 0) {
            timeString = `${days} jour${days > 1 ? 's' : ''}, ${hours}h ${minutes}m ${seconds}s`;
        } else if (hours > 0) {
            timeString = `${hours}h ${minutes}m ${seconds}s`;
        } else if (minutes > 0) {
            timeString = `${minutes}m ${seconds}s`;
        } else {
            timeString = `${seconds}s`;
            countdownElement.classList.add('urgent');
        }

        // Add urgent class when less than 1 hour remains
        if (distance < 3600000) { // 1 hour in milliseconds
            countdownElement.classList.add('urgent');
        }

        countdownElement.innerHTML = timeString;
    }

    // Start countdown if there's an active auction
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.countdown-timer')) {
            updateCountdown(); // Initial update
            setInterval(updateCountdown, 1000); // Update every second
        }
    });
</script>