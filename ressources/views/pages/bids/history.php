<?php

/** @var array $user_bids */
/** @var array $winning_auctions */
/** @var string $page_title */

$base = \App\Core\Config::get('app.base_url');
$currentUserId = (int)$_SESSION['user']['id'];
?>

<link rel="stylesheet" href="<?= $base ?>/assets/css/bid-system.css">

<div class="bid-history-page">
    <div class="page-header">
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <nav class="breadcrumb">
            <a href="<?= $base ?>/dashboard" class="breadcrumb-link">Tableau de bord</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Historique des enchères</span>
        </nav>
    </div>

    <div class="history-content">
        <!-- Enchères en cours où l'utilisateur est en tête -->
        <?php if (!empty($winning_auctions)): ?>
            <section class="winning-section">
                <h2>🎉 Enchères où vous êtes en tête</h2>
                <div class="winning-auctions">
                    <?php foreach ($winning_auctions as $auction): ?>
                        <div class="winning-auction-card">
                            <div class="auction-info">
                                <h3>
                                    <a href="<?= $base ?>/auctions/show?id=<?= $auction['id'] ?>">
                                        <?= htmlspecialchars($auction['stamp_name']) ?>
                                    </a>
                                </h3>
                                <div class="auction-meta">
                                    <span class="winning-bid">Votre offre: <?= number_format($auction['winning_bid'], 2) . ' $ CAD' ?></span>
                                    <span class="auction-end">Fin: <?= date('d/m/Y \u00e0 H:i', strtotime($auction['auction_end'])) ?></span>
                                </div>
                            </div>
                            <div class="auction-actions">
                                <a href="<?= $base ?>/auctions/show?id=<?= $auction['id'] ?>"
                                    class="button button--primary button--small">
                                    Voir l'enchère
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Historique complet des offres -->
        <section class="history-section">
            <h2>Historique de toutes vos offres</h2>

            <?php if (empty($user_bids)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3>Aucune offre pour le moment</h3>
                    <p>Vous n'avez pas encore participé à des enchères.</p>
                    <a href="<?= $base ?>/auctions" class="button button--primary">
                        Découvrir les enchères
                    </a>
                </div>
            <?php else: ?>
                <div class="bid-history-table">
                    <div class="table-header">
                        <div class="header-cell">Timbre</div>
                        <div class="header-cell">Votre offre</div>
                        <div class="header-cell">Date</div>
                        <div class="header-cell">Statut</div>
                        <div class="header-cell">Actions</div>
                    </div>

                    <?php foreach ($user_bids as $bid): ?>
                        <?php
                        $auctionStart = new DateTime($bid['auction_start']);
                        $auctionEnd = new DateTime($bid['auction_end']);
                        $now = new DateTime();

                        $isActive = $now >= $auctionStart && $now <= $auctionEnd;
                        $hasEnded = $now > $auctionEnd;

                        // Déterminer le statut de l'offre (simplifié)
                        $status = 'En cours';
                        $statusClass = 'active';

                        if ($hasEnded) {
                            $status = 'Terminée';
                            $statusClass = 'ended';
                        }
                        ?>
                        <div class="table-row">
                            <div class="table-cell">
                                <div class="stamp-info">
                                    <strong><?= htmlspecialchars($bid['stamp_name']) ?></strong>
                                    <small>Fin: <?= $auctionEnd->format('d/m/Y H:i') ?></small>
                                </div>
                            </div>

                            <div class="table-cell">
                                <span class="bid-amount"><?= number_format($bid['price'], 2) . ' $ CAD' ?></span>
                            </div>

                            <div class="table-cell">
                                <time><?= date('d/m/Y H:i', strtotime($bid['bid_at'])) ?></time>
                            </div>

                            <div class="table-cell">
                                <span class="bid-status-badge <?= $statusClass ?>"><?= $status ?></span>
                            </div>

                            <div class="table-cell">
                                <a href="<?= $base ?>/auctions/show?id=<?= $bid['auction_id'] ?>"
                                    class="button button--secondary button--small">
                                    Voir
                                </a>

                                <?php if ($isActive): ?>
                                    <form action="<?= $base ?>/bid/delete" method="post"
                                        style="display: inline-block; margin-left: 5px;"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cette offre ?')">
                                        <input type="hidden" name="_token" value="<?= \App\Core\CsrfToken::token() ?>">
                                        <input type="hidden" name="id" value="<?= $bid['id'] ?>">
                                        <input type="hidden" name="auction_id" value="<?= $bid['auction_id'] ?>">
                                        <button type="submit" class="button button--danger button--small">
                                            Retirer
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- Styles moved to ressources/scss/pages/_bid-history.scss -->