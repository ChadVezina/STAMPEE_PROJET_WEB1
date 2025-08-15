<?php
/** @var array $auctions */
/** @var int $page */
/** @var int $pages */
$base = \App\Core\Config::get('app.base_url');
?>
<section class="auctions">
  <header class="auctions__header">
    <h1 class="auctions__title">Enchères en cours</h1>

    <div class="auctions__tools">
      <form class="auctions__search" action="<?= $base ?>/auctions" method="get">
        <input class="auctions__input" type="text" name="q" placeholder="Rechercher…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button class="button button--primary auctions__btn" type="submit">Rechercher</button>
      </form>
      <div class="auctions__filters">
        <!-- Placeholders UI (non bloquant) -->
        <button class="button button--ghost">Afficher plus</button>
        <button class="button button--ghost">Filtrer par</button>
      </div>
    </div>
  </header>

  <div class="grid grid--cards grid--with-aside">
    <div class="grid__main">
      <?php foreach ($auctions as $a): ?>
        <article class="card card--auction">
          <a class="card__link" href="<?= $base ?>/stamp/show?id=<?= (int)$a['stamp_id'] ?>">
            <div class="card__image" style="background-image:url('<?= htmlspecialchars($a['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
            <h3 class="card__title"><?= htmlspecialchars($a['stamp_name'] ?? 'Timbre') ?></h3>
            <p class="card__price">
              <?= isset($a['current_price']) && $a['current_price'] ? number_format((float)$a['current_price'], 2) . ' $ CAD' : number_format((float)$a['min_price'], 2) . ' $ CAD' ?>
            </p>
          </a>
        </article>
      <?php endforeach; ?>
    </div>

    <aside class="grid__aside">
      <!-- Barre latérale (placeholder) -->
      <div class="aside-card">
        <h4 class="aside-card__title">Raccourcis</h4>
        <ul class="aside-card__list">
          <li><a href="<?= $base ?>/">Accueil</a></li>
          <li><a href="<?= $base ?>/auctions">Toutes les enchères</a></li>
        </ul>
      </div>
    </aside>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
      <span class="pagination__info">Page&nbsp;: <?= $page ?> / <?= $pages ?></span>
      <ul class="pagination__list">
        <?php for ($i=1; $i<=$pages; $i++): ?>
          <li class="pagination__item">
            <a class="pagination__link<?= $i===$page ? ' pagination__link--active':'' ?>" href="<?= $base ?>/auctions?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

  <footer class="auctions__footer">© STAMPEE 2025</footer>
</section>