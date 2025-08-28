<?php

/** @var array $auction */
/** @var array $bids */
/** @var array $auction_stats */
/** @var bool $user_can_bid */
/** @var bool $user_is_winning */
/** @var float $minimum_bid */
/** @var bool $is_active */

$base = \App\Core\Config::get('app.base_url');

// Format auction dates
$startDate = new DateTime($auction['auction_start']);
$endDate = new DateTime($auction['auction_end']);
$now = new DateTime();

$hasEnded = $now > $endDate;
$hasStarted = $now >= $startDate;
$isActive = $is_active; // Use the variable passed from controller

// Get main image
$mainImage = $auction['main_image'] ?? '';

// Current user
$currentUserId = isset($_SESSION['user']) ? (int)$_SESSION['user']['id'] : 0;
$isLoggedIn = $currentUserId > 0;

// Helper function for time ago
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'il y a ' . $time . 's';
    if ($time < 3600) return 'il y a ' . floor($time / 60) . 'min';
    if ($time < 86400) return 'il y a ' . floor($time / 3600) . 'h';
    return 'il y a ' . floor($time / 86400) . 'j';
}
?>

<link rel="stylesheet" href="<?= $base ?>/assets/css/main.css">

<div class="auction-detail" data-auction-id="<?= $auction['id'] ?>" data-current-bid="<?= $auction_stats['highest_bid'] ?? $auction['min_price'] ?>">
    <!-- Header avec navigation et statut -->
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
        <!-- Informations principales -->
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

            <div class="auction-info-section">
                <h1 class="auction-title"><?= htmlspecialchars($auction['stamp_name']) ?></h1>

                <!-- Informations de l'enchère -->
                <div class="auction-info">
                    <div class="info-card">
                        <div class="info-label">Prix minimum</div>
                        <div class="info-value price"><?= number_format($auction['min_price'], 2) . ' $ CAD' ?></div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">Offre actuelle</div>
                        <div class="info-value price"><?= number_format($auction_stats['highest_bid'] ?? $auction['min_price'], 2) . ' $ CAD' ?></div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">Nombre d'offres</div>
                        <div class="info-value"><?= $auction_stats['total_bids'] ?></div>
                    </div>

                    <?php if ($isActive): ?>
                        <div class="info-card">
                            <div class="info-label">Temps restant</div>
                            <div class="info-value countdown-timer" data-end-time="<?= date('c', strtotime($auction['auction_end'])) ?>">
                                Calcul en cours...
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Vendeur et dates -->
                <div class="auction-meta">
                    <div class="meta-item">
                        <strong>Vendeur:</strong>
                        <?= htmlspecialchars($auction['seller_name'] ?? 'Non spécifié') ?>
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

                <!-- Section enchères -->
                <?php if ($isActive && $isLoggedIn): ?>
                    <div class="bid-section">
                        <h3>Faire une enchère</h3>

                        <?php if ($user_is_winning): ?>
                            <div class="winning-notice">
                                🎉 Vous êtes actuellement en tête de cette enchère !
                            </div>
                        <?php endif; ?>

                        <?php if ($user_can_bid): ?>
                            <form id="bid-form" class="bid-form" action="<?= $base ?>/bid/store" method="POST" data-auction-id="<?= $auction['id'] ?>">
                                <input type="hidden" name="_token" value="<?= \App\Core\CsrfToken::token() ?>">
                                <input type="hidden" name="auction_id" value="<?= $auction['id'] ?>">

                                <div class="bid-input-group">
                                    <input type="number"
                                        id="bid-price"
                                        name="price"
                                        class="bid-price-input"
                                        min="<?= $minimum_bid ?>"
                                        step="0.01"
                                        placeholder="Minimum: <?= number_format($minimum_bid, 2) . ' $ CAD' ?>"
                                        required>

                                    <button type="submit" id="bid-submit" class="bid-submit-btn">
                                        Placer l'offre
                                    </button>
                                </div>

                                <div id="bid-errors" class="bid-errors"></div>

                                <!-- Suggestions de montants -->
                                <div id="bid-suggestions"></div>

                                <div class="bid-help">
                                    <small>Montant minimum: <span id="minimum-bid"><?= number_format($minimum_bid, 2) . ' $ CAD' ?></span></small>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="auction-closed">
                                Vous ne pouvez pas miser sur cette enchère.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif (!$hasStarted && $isLoggedIn && $currentUserId !== (int)$auction['seller_id']): ?>
                    <div class="bid-section">
                        <h3>Faire une enchère</h3>
                        <div class="upcoming-notice">
                            <?php
                            $timeRemaining = $startDate->getTimestamp() - $now->getTimestamp();
                            $days = floor($timeRemaining / 86400);
                            $hours = floor(($timeRemaining % 86400) / 3600);
                            $minutes = floor(($timeRemaining % 3600) / 60);
                            $seconds = $timeRemaining % 60;

                            $timeString = '';
                            if ($days > 0) {
                                $timeString = "{$days}j {$hours}h {$minutes}m";
                            } elseif ($hours > 0) {
                                $timeString = "{$hours}h {$minutes}m {$seconds}s";
                            } elseif ($minutes > 0) {
                                $timeString = "{$minutes}m {$seconds}s";
                            } else {
                                $timeString = "{$seconds}s";
                            }
                            ?>
                            <p>Désolé cette enchère n'est pas encore active vous ne pouvez donc pas Miser avant <?= $timeString ?></p>
                        </div>
                    </div>
                <?php elseif (!$isLoggedIn && !$hasEnded): ?>
                    <div class="bid-section">
                        <div class="login-prompt">
                            <p>Connectez-vous pour participer à cette enchère.</p>
                            <a href="<?= $base ?>/login" class="button button--primary">Se connecter</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar avec historique et statistiques -->
        <div class="auction-sidebar">
            <!-- Statistiques -->
            <div id="auction-stats" class="stats-section">
                <h3>Statistiques</h3>
                <div class="auction-stats">
                    <div class="stat">
                        <span class="label">Enchérisseurs uniques:</span>
                        <span class="value"><?= $auction_stats['unique_bidders'] ?></span>
                    </div>
                    <?php if ($auction_stats['average_bid'] > 0): ?>
                        <div class="stat">
                            <span class="label">Offre moyenne:</span>
                            <span class="value"><?= number_format($auction_stats['average_bid'], 2) . ' $ CAD' ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historique des offres -->
            <div class="bid-history">
                <h3 class="bid-history-title">Historique des enchères</h3>

                <?php if (empty($bids)): ?>
                    <p class="no-bids">Aucune enchère pour le moment.</p>
                <?php else: ?>
                    <div class="bid-list">
                        <?php foreach ($bids as $index => $bid): ?>
                            <?php
                            $isWinning = $index === 0; // Premier = plus élevé
                            $isOwnBid = $isLoggedIn && (int)$bid['bidder_id'] === $currentUserId;
                            $bidClasses = '';
                            if ($isWinning) $bidClasses .= ' winning';
                            if ($isOwnBid) $bidClasses .= ' own-bid';
                            ?>
                            <div class="bid-item<?= $bidClasses ?>">
                                <div class="bid-card">
                                    <div class="bidder-info">
                                        <div class="bidder-name">
                                            <?= $isOwnBid ? 'Vous' : htmlspecialchars($bid['bidder_name']) ?>
                                            <?php if ($isWinning): ?>
                                                <span class="bid-status-badge winning">En tête</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="bid-meta">
                                            <div class="bid-time">
                                                <?= date('d/m/Y à H:i:s', strtotime($bid['bid_at'])) ?>
                                            </div>
                                            <div class="time-ago"><?= timeAgo($bid['bid_at']) ?></div>
                                        </div>
                                    </div>
                                    <div class="bid-amount-section">
                                        <div class="bid-amount <?= $isWinning ? 'winning' : '' ?>">
                                            <?= number_format($bid['price'], 2) . ' $ CAD' ?>
                                        </div>
                                        <?php if ($index > 0 && isset($bids[$index])): ?>
                                            <?php
                                            $previousBid = $bids[$index];
                                            if (isset($previousBid['price'])) {
                                                $increase = (($bid['price'] - $previousBid['price']) / $previousBid['price']) * 100;
                                                if ($increase > 0) {
                                                    echo '<span class="increase-percent">+' . number_format($increase, 1) . '%</span>';
                                                }
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bid-manager.js"></script>

<script>
    // Countdown Timer 
    function updateCountdown() {
        const countdownElement = document.querySelector('.countdown-timer');
        if (!countdownElement) return;

        const endTime = new Date(countdownElement.getAttribute('data-end-time')).getTime();
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            countdownElement.innerHTML = "Terminée";
            countdownElement.classList.add('danger');
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
            countdownElement.classList.add('warning');
        } else {
            timeString = `${seconds}s`;
            countdownElement.classList.add('danger');
        }

        countdownElement.innerHTML = timeString;
    }

    // Start countdown
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.countdown-timer')) {
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // Bid system loaded
    });
</script>