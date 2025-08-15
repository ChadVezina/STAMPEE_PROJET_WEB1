<?php

/** @var array $stamp */
/** @var array|null $auction */
/** @var array $bids */
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
<section class="stamp">
    <header class="stamp__header">
        <h1 class="stamp__title"><?= htmlspecialchars($stamp['name']) ?></h1>
    </header>

    <div class="stamp__gallery">
        <div class="stamp__main" style="background-image:url('<?= htmlspecialchars($main, ENT_QUOTES) ?>');"></div>
        <?php if (!empty($stamp['images'])): ?>
            <div class="stamp__thumbs">
                <?php foreach ($stamp['images'] as $img): ?>
                    <div class="stamp__thumb" style="background-image:url('<?= htmlspecialchars($img['url'], ENT_QUOTES) ?>');"></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="stamp__cta">
        <?php if ($auction): ?>
            <div class="stamp__price">
                <span class="stamp__price-value">
                    <?= number_format((float)($auction['current_price'] ?: $auction['min_price']), 2) ?> $ CAD
                </span>
                <span class="stamp__countdown">Temps restant: <!-- JS countdown ici si souhaité --></span>
            </div>
            <a href="<?= $base ?>/auctions" class="button button--primary stamp__bid">Enchérir</a>
        <?php else: ?>
            <p class="stamp__soldout">Aucune enchère active pour ce timbre.</p>
        <?php endif; ?>
    </div>

    <section class="stamp__details">
        <h2 class="stamp__subtitle">Détails</h2>
        <ul class="stamp__list">
            <?php if (!empty($stamp['current_state'])): ?>
                <li class="stamp__item"><strong>Condition:</strong> <?= htmlspecialchars($stamp['current_state']) ?></li>
            <?php endif; ?>
            <?php if (!empty($stamp['country_name'])): ?>
                <li class="stamp__item"><strong>Pays d’origine:</strong> <?= htmlspecialchars($stamp['country_name']) ?></li>
            <?php elseif (!empty($stamp['country_code'])): ?>
                <li class="stamp__item"><strong>Pays:</strong> <?= htmlspecialchars($stamp['country_code']) ?></li>
            <?php endif; ?>
            <?php if (!empty($stamp['nbr_stamps'])): ?>
                <li class="stamp__item"><strong>Nombre d’exemplaires produit:</strong> <?= (int)$stamp['nbr_stamps'] ?></li>
            <?php endif; ?>
            <?php
            $dims = [];
            if (!empty($stamp['width_mm']))  $dims[]  = rtrim(rtrim((string)$stamp['width_mm'], '0'), '.') . ' mm';
            if (!empty($stamp['height_mm'])) $dims[] = rtrim(rtrim((string)$stamp['height_mm'], '0'), '.') . ' mm';
            $dimTxt = !empty($dims) ? implode(' x ', $dims) : ($stamp['dimensions'] ?? '');
            ?>
            <?php if ($dimTxt): ?>
                <li class="stamp__item"><strong>Dimensions:</strong> <?= htmlspecialchars($dimTxt) ?></li>
            <?php endif; ?>
            <li class="stamp__item"><strong>Certifié:</strong> <?= !empty($stamp['certified']) ? 'Oui' : 'Non' ?></li>
        </ul>
        <a class="button button--ghost" href="#">Parcourir documents</a>
    </section>

    <?php if ($bids): ?>
        <section class="stamp__bids">
            <h2 class="stamp__subtitle">Offres récentes</h2>
            <ul class="stamp__bidlist">
                <?php foreach ($bids as $b): ?>
                    <li class="stamp__biditem">
                        <span><?= htmlspecialchars($b['bidder_name']) ?></span>
                        <strong><?= number_format((float)$b['price'], 2) ?> $</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <footer class="stamp__footer">© STAMPEE 2025</footer>
</section>