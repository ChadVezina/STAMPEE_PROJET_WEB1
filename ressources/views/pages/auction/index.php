<?php

/** @var array $auctions */
/** @var int $page */
/** @var int $pages */
$base = \App\Core\Config::get('app.base_url');
?>
<section class="auctions">
  <header class="auctions__header">
    <h1 class="auctions__title">Toutes les enchères</h1>

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

  <div class="auctions-grid">
    <?php if (!empty($auctions)): ?>
      <?php foreach ($auctions as $a): ?>
        <?php
        $isUpcoming = strtotime($a['auction_start']) > time();
        $isActive = strtotime($a['auction_start']) <= time() && strtotime($a['auction_end']) > time();
        ?>
        <article class="auction-card">
          <a class="auction-card__link" href="<?= $base ?>/auctions/show?id=<?= (int)$a['id'] ?>">
            <div class="auction-card__image" style="background-image:url('<?= htmlspecialchars($a['main_image'] ?? '', ENT_QUOTES) ?>');">
              <?php if ($isUpcoming): ?>
                <span class="auction-card__badge auction-card__badge--upcoming">À venir</span>
              <?php elseif ($isActive): ?>
                <span class="auction-card__badge auction-card__badge--active">En cours</span>
              <?php else: ?>
                <span class="auction-card__badge auction-card__badge--ended">Terminée</span>
              <?php endif; ?>
            </div>
            <div class="auction-card__content">
              <h3 class="auction-card__title"><?= htmlspecialchars($a['stamp_name'] ?? 'Timbre') ?></h3>
              <div class="auction-card__price">
                <?= isset($a['current_price']) && $a['current_price'] ? number_format((float)$a['current_price'], 2) . ' $ CAD' : number_format((float)$a['min_price'], 2) . ' $ CAD' ?>
              </div>
              <?php if (!empty($a['auction_end'])): ?>
                <div class="auction-card__countdown" data-end-time="<?= date('c', strtotime($a['auction_end'])) ?>">
                  <span class="countdown-text">Calcul en cours...</span>
                </div>
              <?php endif; ?>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-auctions">
        <p>Aucune enchère disponible pour le moment.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pagination des enchères">
      <div class="pagination__info">
        Page <?= $page ?> sur <?= $pages ?> (<?= $total ?> enchères au total)
      </div>

      <div class="pagination__controls">
        <?php if ($page > 1): ?>
          <a href="<?= $base ?>/auctions?page=<?= $page - 1 ?>" class="pagination__nav pagination__nav--prev" aria-label="Page précédente">
            ‹ Précédent
          </a>
        <?php endif; ?>

        <div class="pagination__numbers">
          <?php
          $start = max(1, $page - 2);
          $end = min($pages, $page + 2);

          if ($start > 1): ?>
            <a href="<?= $base ?>/auctions?page=1" class="pagination__link">1</a>
            <?php if ($start > 2): ?>
              <span class="pagination__ellipsis">...</span>
            <?php endif; ?>
          <?php endif; ?>

          <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?= $base ?>/auctions?page=<?= $i ?>"
              class="pagination__link<?= $i === $page ? ' pagination__link--active' : '' ?>"
              aria-label="Page <?= $i ?>"
              aria-current="<?= $i === $page ? 'page' : null ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($end < $pages): ?>
            <?php if ($end < $pages - 1): ?>
              <span class="pagination__ellipsis">...</span>
            <?php endif; ?>
            <a href="<?= $base ?>/auctions?page=<?= $pages ?>" class="pagination__link"><?= $pages ?></a>
          <?php endif; ?>
        </div>

        <?php if ($page < $pages): ?>
          <a href="<?= $base ?>/auctions?page=<?= $page + 1 ?>" class="pagination__nav pagination__nav--next" aria-label="Page suivante">
            Suivant ›
          </a>
        <?php endif; ?>
      </div>
    </nav>
  <?php endif; ?>

  <footer class="auctions__footer">© STAMPEE 2025</footer>
</section>

<!-- CSS styles moved to ressources/scss/components/_countdown.scss -->

<script>
  // Countdown Timer Functionality for Auction Cards
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