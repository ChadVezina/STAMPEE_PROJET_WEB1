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
          <a class="card__link" href="<?= $base ?>/stamps/show?id=<?= (int)$a['stamp_id'] ?>">
            <div class="card__image" style="background-image:url('<?= htmlspecialchars($a['main_image'] ?? '', ENT_QUOTES) ?>');"></div>
            <h3 class="card__title"><?= htmlspecialchars($a['stamp_name'] ?? 'Timbre') ?></h3>
            <p class="card__price">
              <?= isset($a['current_price']) && $a['current_price'] ? number_format((float)$a['current_price'], 2) . ' $ CAD' : number_format((float)$a['min_price'], 2) . ' $ CAD' ?>
            </p>
            <?php if (!empty($a['auction_end'])): ?>
              <div class="card__time-remaining" data-end-time="<?= date('c', strtotime($a['auction_end'])) ?>">
                <span class="card__countdown">Calcul en cours...</span>
              </div>
            <?php endif; ?>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
      <span class="pagination__info">Page&nbsp;: <?= $page ?> / <?= $pages ?></span>
      <ul class="pagination__list">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <li class="pagination__item">
            <a class="pagination__link<?= $i === $page ? ' pagination__link--active' : '' ?>" href="<?= $base ?>/auctions?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

  <footer class="auctions__footer">© STAMPEE 2025</footer>
</section>

<!-- CSS styles moved to ressources/scss/components/_countdown.scss -->

<script>
  // Countdown Timer Functionality for Auction Cards
  function updateCountdowns() {
    const countdownElements = document.querySelectorAll('.card__countdown');

    countdownElements.forEach(element => {
      const timeRemaining = element.closest('.card__time-remaining');
      if (!timeRemaining) return;

      const endTime = new Date(timeRemaining.getAttribute('data-end-time')).getTime();
      const now = new Date().getTime();
      const distance = endTime - now;

      if (distance < 0) {
        element.innerHTML = "Enchère terminée";
        element.classList.add('urgent');
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
        element.classList.add('urgent');
      }

      // Add urgent class when less than 1 hour remains
      if (distance < 3600000) { // 1 hour in milliseconds
        element.classList.add('urgent');
      }

      element.innerHTML = timeString;
    });
  }

  // Start countdown if there are auction cards
  document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.card__countdown')) {
      updateCountdowns(); // Initial update
      setInterval(updateCountdowns, 1000); // Update every second
    }
  });
</script>