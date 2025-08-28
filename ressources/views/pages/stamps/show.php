<?php

/** @var array $stamp */
$base = \App\Core\Config::get('app.base_url');
$main = '';
foreach ($stamp['images'] ?? [] as $img) {
    if (!empty($img['is_main'])) {
        $main = $img['url'];
        break;
    }
}
if (!$main && !empty($stamp['images'][0]['url'])) {
    $main = $stamp['images'][0]['url'];
}
?>

<div class="stamp-showcase">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <div class="breadcrumb-container">
            <a href="<?= $base ?>/auctions" class="breadcrumb-link">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 4.42 3.58 8 8 8 4.42 0 8-3.58 8-8 0-4.42-3.58-8-8-8zm-2 12l-1.41-1.41L9.17 6H4V4h8v8h-2V7.41l-4.59 4.59z" />
                </svg>
                Retour aux enchères
            </a>
            <span class="breadcrumb-separator">•</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($stamp['name']) ?></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="stamp-layout">
            <!-- Image Gallery Section -->
            <div class="stamp-gallery">
                <div class="main-image-container">
                    <?php if ($main): ?>
                        <img src="<?= htmlspecialchars($main) ?>"
                            alt="<?= htmlspecialchars($stamp['name']) ?>"
                            class="main-image">
                    <?php else: ?>
                        <div class="image-placeholder">
                            <div class="placeholder-icon">
                                <svg width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                                </svg>
                            </div>
                            <p>Aucune image disponible</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($stamp['images']) && count($stamp['images']) > 1): ?>
                    <div class="thumbnails-grid">
                        <?php foreach ($stamp['images'] as $img): ?>
                            <button class="thumbnail <?= $img['is_main'] ? 'thumbnail--active' : '' ?>"
                                onclick="switchMainImage('<?= htmlspecialchars($img['url']) ?>')">
                                <img src="<?= htmlspecialchars($img['url']) ?>"
                                    alt="<?= htmlspecialchars($stamp['name']) ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details Section -->
            <div class="stamp-details">
                <!-- Header -->
                <div class="stamp-header">
                    <h1 class="stamp-title"><?= htmlspecialchars($stamp['name']) ?></h1>

                    <div class="action-buttons">
                        <?php
                        // Only show edit button if user is logged in and owns this stamp
                        if (isset($_SESSION['user']['id']) && isset($stamp['user_id']) && $_SESSION['user']['id'] == $stamp['user_id']):
                        ?>
                            <a href="<?= $base ?>/stamps/edit?id=<?= $stamp['id'] ?>"
                                class="btn btn--secondary">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                                </svg>
                                Modifier
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Specifications Card -->
                <div class="info-card">
                    <h3 class="card-title">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16A8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                        </svg>
                        Spécifications
                    </h3>

                    <div class="specs-grid">
                        <?php
                        // Some records store descriptive text in `description` or in `dimensions` (free text).
                        $descr = trim((string)($stamp['description'] ?? $stamp['dimensions'] ?? ''));
                        if ($descr !== ''): ?>
                            <div class="spec-item spec-item--full">
                                <span class="spec-label">Description</span>
                                <span class="spec-value">
                                    <div class="description-text"><?= nl2br(htmlspecialchars($descr)) ?></div>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($stamp['country_name'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Pays d'origine</span>
                                <span class="spec-value">
                                    <span class="flag-icon flag-icon-<?= strtolower($stamp['country_code']) ?>"></span>
                                    <?= htmlspecialchars($stamp['country_name']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stamp['created_at'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Date de création</span>
                                <span class="spec-value"><?= date('d/m/Y', strtotime($stamp['created_at'])) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stamp['width_mm']) && !empty($stamp['height_mm'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Dimensions</span>
                                <span class="spec-value"><?= $stamp['width_mm'] ?> × <?= $stamp['height_mm'] ?> mm</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stamp['current_state'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">État</span>
                                <span class="spec-value">
                                    <span class="state-badge state-badge--<?= strtolower($stamp['current_state']) ?>">
                                        <?= htmlspecialchars($stamp['current_state']) ?>
                                    </span>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stamp['nbr_stamps'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Tirage</span>
                                <span class="spec-value"><?= number_format($stamp['nbr_stamps']) ?> exemplaires</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stamp['certified'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Certification</span>
                                <span class="spec-value">
                                    <span class="certification-badge">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708z" />
                                        </svg>
                                        Certifié authentique
                                    </span>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Current Auctions -->
                <?php
                // Check if there are active auctions for this stamp
                $auctionService = new \App\Services\AuctionService();
                $activeAuction = $auctionService->getActiveByStamp($stamp['id']);
                ?>

                <?php if ($activeAuction): ?>
                    <div class="info-card auction-card">
                        <h3 class="card-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z" />
                            </svg>
                            Enchère en cours
                        </h3>

                        <div class="auction-info">
                            <div class="auction-price">
                                <span class="current-price">
                                    <?= $activeAuction['current_price'] > 0
                                        ? number_format($activeAuction['current_price'], 2) . ' $ CAD'
                                        : number_format($activeAuction['min_price'], 2) . ' $ CAD (prix de départ)'
                                    ?>
                                </span>
                            </div>

                            <div class="auction-time">
                                <div class="time-remaining" data-end-time="<?= date('c', strtotime($activeAuction['auction_end'])) ?>">
                                    <span class="countdown-timer">Calcul en cours...</span>
                                </div>
                                <div class="end-date">
                                    Se termine le <?= date('d/m/Y à H:i', strtotime($activeAuction['auction_end'])) ?>
                                </div>
                            </div>

                            <a href="<?= $base ?>/auctions/show?id=<?= $activeAuction['id'] ?>"
                                class="btn btn--primary btn--full">
                                Voir l'enchère
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Styles for this page are located in: ressources/scss/pages/_stamp.scss and ressources/scss/components/_countdown.scss -->

<script src="<?= $base ?>/public/assets/js/stamp-show.js"></script>