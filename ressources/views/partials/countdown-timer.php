<?php

/**
 * Reusable Countdown Timer Component
 * 
 * This component provides HTML, CSS, and JavaScript for countdown timers.
 * Can be included in any view that needs countdown functionality.
 * 
 * Usage:
 * 1. Include this file: <?php include 'path/to/countdown-timer.php'; ?>
 * 2. Add HTML structure with data-end-time attribute
 * 3. Call initializeCountdowns() when DOM is ready
 */
?>

<!-- CSS styles moved to ressources/scss/components/_countdown.scss -->

<script src="<?= htmlspecialchars($basePath ?? \App\Core\Config::get('app.base_url')) ?>/public/assets/js/countdown.js"></script>