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

<?php
// Determine page type server-side so CSS can target containers immediately
// Normalize request and base path to detect page type reliably when app is in a subfolder
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/');
$basePathNormalized = rtrim($basePath, ' /');

$pageType = 'default';

// Home: request equals base path or root
if ($requestPath === '' || $requestPath === '/' || $requestPath === $basePathNormalized || $requestPath === $basePathNormalized . '/index.php') {
  $pageType = 'home';
} elseif (strpos($requestPath, $basePathNormalized . '/dashboard') === 0 || strpos($requestPath, '/dashboard') === 0) {
  $pageType = 'dashboard';
} elseif ((strpos($requestPath, $basePathNormalized . '/auctions') === 0 || strpos($requestPath, '/auctions') === 0) && strpos($requestPath, '/show') === false) {
  $pageType = 'auctions';
}
?>

<body data-page="<?= htmlspecialchars($pageType, ENT_QUOTES) ?>">
  <header class="main-header">
    <div class="header-top">
      <div class="header-brand">
        <h1 class="brand-title">
          <a href="<?= htmlspecialchars($basePath) ?>/">Stampee</a>
        </h1>
      </div>
      <div class="header-profile">
        <?php if (!empty($_SESSION['user'])): ?>
          <div class="profile-dropdown">
            <button class="profile-dropdown__toggle" aria-label="Menu utilisateur" aria-expanded="false">
              <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/user-placeholder.svg" alt="Profil utilisateur" class="profile-image">
              <span class="profile-dropdown__caret">▼</span>
            </button>
            <div class="profile-dropdown__menu">
              <div class="profile-dropdown__header">
                <strong><?= htmlspecialchars($_SESSION['user']['nom']) ?></strong>
                <span><?= htmlspecialchars($_SESSION['user']['email']) ?></span>
              </div>
              <div class="profile-dropdown__divider"></div>
              <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=add-stamp" class="profile-dropdown__item">
                <span class="profile-dropdown__icon">🏷️</span>
                Ajouter un timbre
              </a>
              <a href="<?= htmlspecialchars($basePath) ?>/dashboard?mode=profile" class="profile-dropdown__item">
                <span class="profile-dropdown__icon">👤</span>
                Mon profil
              </a>
              <div class="profile-dropdown__divider"></div>
              <a href="<?= htmlspecialchars($basePath) ?>/logout" class="profile-dropdown__item profile-dropdown__item--danger">
                <span class="profile-dropdown__icon">🚪</span>
                Déconnexion
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="profile-placeholder">
            <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/user-placeholder.svg" alt="Profil utilisateur" class="profile-image">
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="header-separator"></div>

    <nav class="header-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link">Accueil</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= htmlspecialchars($basePath) ?>/auctions" class="nav-link">Enchères</a>
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
          <li><a href="<?= htmlspecialchars($basePath) ?>/lord/login" class="footer-link footer-link--lord" title="Accès Lord">👑</a></li>
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
  <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/lord-favorites.css">

  <style>
    .footer-link--lord {
      opacity: 0.6;
      transition: opacity 0.2s ease;
      font-size: 1.2em;
    }

    .footer-link--lord:hover {
      opacity: 1;
      text-decoration: none;
    }
  </style>

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

  <?php
  // Include auction filter on auction pages
  if (strpos($currentPage, '/auctions') !== false && strpos($currentPage, '/show') === false): ?>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/auction-filter.js" defer></script>
  <?php endif; ?>

  <!-- Profile dropdown functionality -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const dropdownToggle = document.querySelector('.profile-dropdown__toggle');
      const dropdownMenu = document.querySelector('.profile-dropdown__menu');

      if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const isActive = dropdownMenu.classList.contains('profile-dropdown__menu--active');
          dropdownMenu.classList.toggle('profile-dropdown__menu--active');
          dropdownToggle.setAttribute('aria-expanded', !isActive);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
          // If we click on a link inside the dropdown (or its children), let it navigate
          const clickedLink = e.target.closest('a');
          if (clickedLink && dropdownMenu.contains(clickedLink)) {
            dropdownMenu.classList.remove('profile-dropdown__menu--active');
            dropdownToggle.setAttribute('aria-expanded', 'false');
            return; // Let the link navigate
          }

          if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('profile-dropdown__menu--active');
            dropdownToggle.setAttribute('aria-expanded', 'false');
          }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            dropdownMenu.classList.remove('profile-dropdown__menu--active');
            dropdownToggle.setAttribute('aria-expanded', 'false');
          }
        });
      }
    });

    // Password toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      const toggleButtons = document.querySelectorAll('.field__toggle');

      toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();

          const wrapper = this.closest('.field__input-wrapper');
          const input = wrapper.querySelector('.field__input');
          const hideIcon = this.querySelector('.field__toggle-icon--hide');
          const showIcon = this.querySelector('.field__toggle-icon--show');

          if (input.type === 'password') {
            input.type = 'text';
            this.classList.add('is-visible');
            this.setAttribute('aria-label', 'Masquer le mot de passe');
          } else {
            input.type = 'password';
            this.classList.remove('is-visible');
            this.setAttribute('aria-label', 'Afficher le mot de passe');
          }
        });
      });
    });
  </script>

  <script>
    // Set page type for container styling
    document.addEventListener('DOMContentLoaded', function() {
      const path = window.location.pathname;
      const body = document.body;

      if (path === '/' || path.endsWith('/home')) {
        body.setAttribute('data-page', 'home');
      } else if (path.includes('/dashboard')) {
        body.setAttribute('data-page', 'dashboard');
      } else if (path.includes('/auctions') && !path.includes('/show')) {
        body.setAttribute('data-page', 'auctions');
      }
    });
  </script>

  <script src="<?= \App\Core\Config::get('app.base_url') ?>/public/assets/js/forms.js" defer></script>
</body>

</html>