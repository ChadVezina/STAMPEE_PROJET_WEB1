<?php

/** @var array $auctions */
$base = \App\Core\Config::get('app.base_url');
?>
<meta name="base-url" content="<?= $base ?>">

<section class="auctions">
  <header class="auctions__header">
    <h1 class="auctions__title">Toutes les enchères</h1>

    <div class="auctions__tools">
      <!-- This will be replaced by JavaScript with dynamic filters -->
      <div class="auctions__search-placeholder">
        <input class="auctions__input" type="text" placeholder="Rechercher…" disabled>
        <button class="button button--primary" disabled>Rechercher</button>
      </div>
    </div>
  </header>

  <div id="auctions-container">
    <!-- Loading state -->
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des enchères...</p>
    </div>

    <!-- Auction results will be dynamically inserted here -->
    <div class="auctions-grid" style="display: none;"></div>

    <!-- Pagination will be dynamically inserted here -->
    <nav class="pagination" style="display: none;"></nav>
  </div>

  <footer class="auctions__footer">© STAMPEE 2025</footer>
</section>

<!-- Embed auction data as JSON for client-side processing -->
<script type="application/json" id="auction-data">
  <?= json_encode($auctions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

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

  // Make updateCountdowns globally available
  window.updateCountdowns = updateCountdowns;

  // Start countdown if there are auction cards
  document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.auction-card__countdown')) {
      updateCountdowns(); // Initial update
      setInterval(updateCountdowns, 1000); // Update every second
    }
  });
</script>