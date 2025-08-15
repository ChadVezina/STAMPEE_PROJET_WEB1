<?php
$basePath = \App\Core\Config::get('app.base_path', '');
$baseUrl = \App\Core\Config::get('app.base_url', '');
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <title>Stampee</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="<?= htmlspecialchars($baseUrl) ?>/assets/css/main.css" rel="stylesheet">
</head>

<body>
  <header class="main-header">
    <div class="header-top">
      <div class="header-brand">
        <h1 class="brand-title">
          <a href="<?= htmlspecialchars($basePath) ?>/">Stampee</a>
        </h1>
      </div>
      <div class="header-profile">
        <div class="profile-placeholder">
          <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/user-placeholder.svg" alt="Profil utilisateur" class="profile-image">
        </div>
      </div>
    </div>

    <div class="header-separator"></div>

    <nav class="header-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link">Accueil</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= htmlspecialchars($basePath) ?>/dashboard" class="nav-link">Tableau de bord</a>
        <a href="<?= htmlspecialchars($basePath) ?>/auctions" class="nav-link">Enchères</a>
        <a href="<?= htmlspecialchars($basePath) ?>/logout" class="nav-link">Déconnexion</a>
      <?php else: ?>
        <a href="<?= htmlspecialchars($basePath) ?>/auctions" class="nav-link">Enchères</a>
        <a href="<?= htmlspecialchars($basePath) ?>/login" class="nav-link">Connexion</a>
        <a href="<?= htmlspecialchars($basePath) ?>/register" class="nav-link">Inscription</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
    <?php require VIEW_PATH . '/partials/flash.php'; ?>
    <?= $content ?? '' ?>
  </main>

  <footer class="main-footer">
    <div class="footer-separator"></div>

    <div class="footer-content">
      <div class="footer-section">
        <h3 class="footer-brand">Stampee</h3>
        <p class="footer-description">
          Votre plateforme de référence d'enchères de timbres, fièrement soutenu par Lord Stampee.
        </p>
      </div>

      <div class="footer-section">
        <h4 class="footer-title">Support</h4>
        <ul class="footer-links">
          <li><a href="#" class="footer-link">Aide</a></li>
          <li><a href="#" class="footer-link">Contact</a></li>
          <li><a href="#" class="footer-link">Conditions d'utilisation</a></li>
          <li><a href="#" class="footer-link">Politique de confidentialité</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h4 class="footer-title">Suivez-nous</h4>
        <div class="footer-social">
          <a href="#" class="social-link" aria-label="Facebook">📘</a>
          <a href="#" class="social-link" aria-label="Twitter">🐦</a>
          <a href="#" class="social-link" aria-label="Instagram">📷</a>
          <a href="#" class="social-link" aria-label="LinkedIn">💼</a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copyright">
        © <?= date('Y') ?> Stampee. Tous droits réservés.
      </p>
    </div>
  </footer>

  <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/app.js" defer></script>

  <?php
  // Include auth validator on authentication pages
  $currentPage = $_SERVER['REQUEST_URI'] ?? '';
  if (strpos($currentPage, '/login') !== false || strpos($currentPage, '/register') !== false): ?>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/auth-validator.js" defer></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/password-toggle.js" defer></script>
    <script>
      // Initialize auth validator when both scripts are loaded
      document.addEventListener("DOMContentLoaded", () => {
        if (typeof AuthValidator !== 'undefined') {
          window.authValidator = new AuthValidator();
        }
      });
    </script>
  <?php endif; ?>
</body>

</html>