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
        <h2 class="section__title">Coup de Cœur du Lord</h2>
        <p class="section__subtitle">Sélection exclusive de timbres d'exception</p>

        <div class="carousel" role="region" aria-label="Carrousel des coups de cœur">
            <button class="carousel__ctrl carousel__ctrl--prev" aria-label="Précédent">◀</button>
            <div class="carousel__track">
                <?php foreach (array_slice($featured, 0, 3) as $item): ?>
                    <article class="carousel__item card card--featured">
                        <a class="card__link" href="<?= $base ?>/stamp/show?id=<?= (int)$item['stamp_id'] ?>">
                            <div class="card__image" style="background-image:url('<?= htmlspecialchars($item['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
                            <div class="card__content">
                                <h3 class="card__title"><?= htmlspecialchars($item['stamp_name'] ?? 'Timbre') ?></h3>
                                <p class="card__price">
                                    <?= isset($item['current_price']) && $item['current_price'] ? number_format((float)$item['current_price'], 2) . ' $ CAD' : number_format((float)$item['min_price'], 2) . ' $ CAD' ?>
                                </p>
                                <span class="card__badge">Coup de Cœur</span>
                            </div>
                        </a>
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
                            <p class="card__time">Se termine bientôt</p>
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
        <h2 class="section__title">Acquisitions récentes</h2>
        <p class="section__subtitle">Les dernières trouvailles de nos collectionneurs</p>

        <?php if (!empty($running)): ?>
            <?php $recent = $running[0]; // Get the most recent one 
            ?>
            <article class="recent-card">
                <div class="recent-card__content">
                    <div class="recent-card__image" style="background-image:url('<?= htmlspecialchars($recent['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
                    <div class="recent-card__info">
                        <h3 class="recent-card__title"><?= htmlspecialchars($recent['stamp_name'] ?? 'Timbre') ?></h3>
                        <p class="recent-card__description">Acquis récemment par un collectionneur passionné</p>
                        <p class="recent-card__price">
                            <?= isset($recent['current_price']) && $recent['current_price'] ? number_format((float)$recent['current_price'], 2) . ' $ CAD' : number_format((float)$recent['min_price'], 2) . ' $ CAD' ?>
                        </p>
                        <time class="recent-card__time">Il y a quelques instants</time>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>