<?php

/** @var array $featured */
/** @var array $running */
$base = \App\Core\Config::get('app.base_url');
?>

<!-- HERO SECTION (2 columns, full width) -->
<section class="hero">
    <div class="hero__container">
        <!-- HERO SECTION / img du lord (left, 50%) -->
        <div class="hero__image">
            <div class="hero__lord-image" aria-label="Portrait of Lord Stampee"></div>
        </div>

        <!-- HERO SECTION / slogan (right, 50%) -->
        <div class="hero__content">
            <h1 class="hero__title">STAMPEE</h1>
            <p class="hero__slogan">La plateforme d'enchères de timbres de prestige</p>
            <p class="hero__mission">Sous le patronage du distingué Lord Reginald Stampee</p>
            <a href="<?= $base ?>/auctions" class="hero__cta button button--primary">Découvrir les enchères</a>
        </div>
    </div>
</section>

<!-- COUP DE COEUR (full width, 3 card carousel) -->
<section class="coup-de-coeur">
    <div class="section__container">
        <h2 class="section__title">Coups de Cœur du Lord</h2>
        <p class="section__subtitle">Sélection exclusive de timbres et enchères d'exception</p>

        <div class="carousel" role="region" aria-label="Carrousel des coups de cœur">
            <button class="carousel__ctrl carousel__ctrl--prev" aria-label="Précédent">◀</button>
            <div class="carousel__track">
                <?php foreach (array_slice($featured, 0, 6) as $item): ?>
                    <article class="carousel__item card card--featured">
                        <?php if (isset($item['type']) && $item['type'] === 'stamp_favorite'): ?>
                            <!-- Stamp Favorite -->
                            <a class="card__link" href="<?= $base ?>/stamps/public?id=<?= (int)$item['stamp_id'] ?>">
                                <div class="card__image" style="background-image:url('<?= htmlspecialchars($item['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
                                <div class="card__content">
                                    <h3 class="card__title"><?= htmlspecialchars($item['stamp_name'] ?? 'Timbre') ?></h3>
                                    <p class="card__meta">
                                        <?= htmlspecialchars($item['country_name'] ?? 'Pays inconnu') ?>
                                    </p>
                                    <p class="card__owner">
                                        Par <?= htmlspecialchars($item['seller_name'] ?? 'Propriétaire') ?>
                                    </p>
                                    <span class="card__badge card__badge--stamp">✨ Coup de Cœur</span>
                                </div>
                            </a>
                        <?php else: ?>
                            <!-- Auction Favorite (existing) -->
                            <a class="card__link" href="<?= $base ?>/auctions/show?id=<?= (int)$item['id'] ?>">
                                <div class="card__image" style="background-image:url('<?= htmlspecialchars($item['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
                                <div class="card__content">
                                    <h3 class="card__title"><?= htmlspecialchars($item['stamp_name'] ?? 'Timbre') ?></h3>
                                    <p class="card__price">
                                        <?= isset($item['current_price']) && $item['current_price'] ? number_format((float)$item['current_price'], 2) . ' $ CAD' : number_format((float)$item['min_price'], 2) . ' $ CAD' ?>
                                    </p>
                                    <?php if (!empty($item['auction_end'])): ?>
                                        <div class="card__time-remaining" data-end-time="<?= date('c', strtotime($item['auction_end'])) ?>">
                                            <span class="card__countdown">Calcul en cours...</span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="card__badge card__badge--auction">🔥 Enchère Coup de Cœur</span>
                                </div>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <button class="carousel__ctrl carousel__ctrl--next" aria-label="Suivant">▶</button>
        </div>
    </div>
</section>

<!-- CURRENT OFFERS (full width, 3 columns 2 rows) -->
<section class="current-offers">
    <div class="section__container">
        <h2 class="section__title">Offres en cours</h2>
        <p class="section__subtitle">Enchères actives • Participez maintenant</p>

        <div class="offers-grid">
            <?php foreach (array_slice($running, 0, 6) as $auction): ?>
                <article class="card card--auction">
                    <a class="card__link" href="<?= $base ?>/auctions/show?id=<?= (int)$auction['id'] ?>">
                        <div class="card__image" style="background-image:url('<?= htmlspecialchars($auction['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
                        <div class="card__content">
                            <h3 class="card__title"><?= htmlspecialchars($auction['stamp_name'] ?? 'Timbre') ?></h3>
                            <p class="card__price">
                                <?= isset($auction['current_price']) && $auction['current_price'] ? number_format((float)$auction['current_price'], 2) . ' $ CAD' : number_format((float)$auction['min_price'], 2) . ' $ CAD' ?>
                            </p>
                            <?php if (!empty($auction['auction_end'])): ?>
                                <div class="card__time-remaining" data-end-time="<?= date('c', strtotime($auction['auction_end'])) ?>">
                                    <span class="card__countdown">Calcul en cours...</span>
                                </div>
                            <?php else: ?>
                                <p class="card__time">Se termine bientôt</p>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="section__actions">
            <a href="<?= $base ?>/auctions" class="button button--outline">Voir toutes les enchères</a>
        </div>
    </div>
</section>

<!-- LORD PRESENTATION (full width, text section) -->
<section class="lord-presentation">
    <div class="section__container">
        <div class="presentation__content">
            <h2 class="section__title">À propos du Lord Stampee</h2>
            <div class="presentation__text">
                <p>Lord Reginald Stampee, figure emblématique du monde philatélique, a consacré sa vie à la collection et à l'expertise des timbres les plus rares au monde. Fort de plus de 40 ans d'expérience, il partage aujourd'hui sa passion à travers cette plateforme d'enchères exclusive.</p>

                <p>Chaque timbre présenté sur Stampee est soigneusement sélectionné selon les critères d'excellence du Lord : authenticité garantie, rareté exceptionnelle et valeur historique indéniable.</p>

                <p>Rejoignez la communauté des collectionneurs prestigieux et découvrez des pièces uniques sous le regard expert de Lord Stampee.</p>
            </div>
        </div>
    </div>
</section>

<!-- RECENT (full width, last card acquired datetime, CARD) -->
<section class="recent-acquisitions">
    <div class="section__container">
        <h2 class="section__title">Actualités</h2>
        <p class="section__subtitle">Les dernières mises de nos collectionneurs</p>

        <?php if (!empty($recentBids)): ?>
            <div class="news-grid">
                <?php foreach ($recentBids as $bid): ?>
                    <article class="recent-card">
                        <a class="recent-card__link" href="<?= $base ?>/auctions/show?id=<?= (int)$bid['auction_id'] ?>">
                            <div class="recent-card__content">
                                <div class="recent-card__image" style="background-image:url('<?= htmlspecialchars($bid['stamp_image'] ?? '', ENT_QUOTES) ?>');"></div>
                                <div class="recent-card__info">
                                    <h3 class="recent-card__title"><?= htmlspecialchars($bid['stamp_name'] ?? 'Timbre') ?></h3>
                                    <p class="recent-card__description">
                                        <?= htmlspecialchars($bid['bidder_name']) ?> a récemment miser <?= number_format((float)$bid['price'], 2) ?> $ CAD
                                    </p>
                                    <p class="recent-card__price">
                                        Mise de <?= number_format((float)$bid['price'], 2) ?> $ CAD
                                    </p>
                                    <time class="recent-card__time" data-last-bid-time="<?= htmlspecialchars($bid['bid_at']) ?>">
                                        Il y a quelques instants
                                    </time>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-news">Aucune activité récente pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Carousel JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Carousel functionality for "Coups de Cœur du Lord"
        const carousel = document.querySelector('.coup-de-coeur .carousel');
        if (!carousel) return;

        const track = carousel.querySelector('.carousel__track');
        const items = carousel.querySelectorAll('.carousel__item');
        const prevBtn = carousel.querySelector('.carousel__ctrl--prev');
        const nextBtn = carousel.querySelector('.carousel__ctrl--next');

        if (!track || items.length === 0 || !prevBtn || !nextBtn) return;

        let currentIndex = 0;
        let autoplayInterval = null; // Properly declare the interval variable
        const totalItems = items.length;
        const itemWidth = 350; // Fixed width from CSS
        const gap = 32; // 2rem gap in pixels

        // Update carousel position and item states
        function updateCarousel() {
            // Calculate transform to center the current item
            const containerWidth = carousel.offsetWidth - 200; // Account for padding
            const centerOffset = containerWidth / 2 - itemWidth / 2;
            const translateX = -currentIndex * (itemWidth + gap) + centerOffset;
            track.style.transform = `translateX(${translateX}px)`;

            // Update item classes and states
            items.forEach((item, index) => {
                // Remove all state classes
                item.classList.remove('carousel__item--active', 'carousel__item--prev', 'carousel__item--next');
                item.querySelector('.card--featured')?.classList.remove('card--featured-active');

                if (index === currentIndex) {
                    // Current item - make it active and prominent
                    item.classList.add('carousel__item--active');
                    item.querySelector('.card--featured')?.classList.add('card--featured-active');
                } else if (index === currentIndex - 1) {
                    // Previous item
                    item.classList.add('carousel__item--prev');
                } else if (index === currentIndex + 1) {
                    // Next item
                    item.classList.add('carousel__item--next');
                }
            });

            // Update button states
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= totalItems - 1;

            // Update button visibility
            prevBtn.style.opacity = currentIndex === 0 ? '0.4' : '1';
            nextBtn.style.opacity = currentIndex >= totalItems - 1 ? '0.4' : '1';
        }

        // Go to next slide
        function nextSlide() {
            if (currentIndex < totalItems - 1) {
                currentIndex++;
                updateCarousel();
            }
        }

        // Go to previous slide
        function prevSlide() {
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        }

        // Start autoplay
        function startAutoplay() {
            // Prevent multiple intervals
            if (autoplayInterval !== null) {
                return;
            }

            autoplayInterval = setInterval(() => {
                if (currentIndex >= totalItems - 1) {
                    currentIndex = 0; // Loop back to start
                } else {
                    currentIndex++;
                }
                updateCarousel();
            }, 5000); // Change every 5 seconds
        }

        // Stop autoplay
        function stopAutoplay() {
            if (autoplayInterval !== null) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }

        // Event listeners
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoplay(); // Stop autoplay when user interacts
            // Restart autoplay after a longer delay to give user time to read
            setTimeout(() => {
                startAutoplay();
            }, 8000); // 8 seconds delay after manual interaction
        });

        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoplay(); // Stop autoplay when user interacts
            // Restart autoplay after a longer delay to give user time to read
            setTimeout(() => {
                startAutoplay();
            }, 8000); // 8 seconds delay after manual interaction
        });

        // Pause autoplay on hover
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', () => {
            // Only restart if not manually controlled recently
            setTimeout(() => {
                startAutoplay();
            }, 2000); // 2 second delay before restarting on mouse leave
        });

        // Initialize
        updateCarousel();
        startAutoplay();
    });
</script><!-- CSS styles moved to ressources/scss/components/_countdown.scss -->

<script>
    // Countdown Timer Functionality for Home Page
    function updateHomeCountdowns() {
        const countdownElements = document.querySelectorAll('.card__countdown');

        countdownElements.forEach(element => {
            const timeRemaining = element.closest('.card__time-remaining');
            if (!timeRemaining) return;

            const endTime = new Date(timeRemaining.getAttribute('data-end-time')).getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                element.innerHTML = "Terminée";
                element.classList.add('urgent');
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) {
                timeString = `${days}j ${hours}h`;
            } else if (hours > 0) {
                timeString = `${hours}h ${minutes}m`;
            } else if (minutes > 0) {
                timeString = `${minutes}m ${seconds}s`;
            } else {
                timeString = `${seconds}s`;
                element.classList.add('urgent');
            }

            // Add urgent class when less than 1 hour remains
            if (distance < 3600000) { // 1 hour in milliseconds
                element.classList.add('urgent');
            }

            element.innerHTML = timeString;
        });
    }

    // Start countdown if there are countdown elements
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.card__countdown')) {
            updateHomeCountdowns(); // Initial update
            setInterval(updateHomeCountdowns, 1000); // Update every second
        }
    });
</script>

<script>
    // Met à jour les éléments <time data-last-bid-time> en texte relatif simple
    function pluralize(n, singular, plural) {
        return n + ' ' + (n > 1 ? plural : singular);
    }

    function updateRecentCardTimes() {
        const els = document.querySelectorAll('time.recent-card__time[data-last-bid-time]');
        els.forEach(el => {
            const ts = el.getAttribute('data-last-bid-time');
            if (!ts) return;
            const date = new Date(ts);
            if (isNaN(date.getTime())) return;

            const now = Date.now();
            let diff = Math.floor((now - date.getTime()) / 1000); // seconds

            const days = Math.floor(diff / 86400);
            diff %= 86400;
            const hours = Math.floor(diff / 3600);
            diff %= 3600;
            const minutes = Math.floor(diff / 60);

            const parts = [];
            if (days > 0) parts.push(pluralize(days, 'jour', 'jours'));
            if (hours > 0) parts.push(pluralize(hours, 'heure', 'heures'));
            if (minutes > 0) parts.push(pluralize(minutes, 'minute', 'minutes'));

            if (parts.length === 0) {
                el.textContent = "Il y a moins d'une minute";
            } else {
                el.textContent = 'Il y a ' + parts.join(' ');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateRecentCardTimes();
        setInterval(updateRecentCardTimes, 60000); // rafraîchir chaque minute
    });
</script>