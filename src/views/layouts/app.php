<?php $base = \App\Core\Config::get('app.base_url', ''); ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Stampee</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="<?= htmlspecialchars($base) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
<header>
  <nav>
    <a href="<?= htmlspecialchars($base) ?>/">Accueil</a>
    <?php if (!empty($_SESSION['user'])): ?>
      <a href="<?= htmlspecialchars($base) ?>/dashboard">Tableau de bord</a>
      <a href="<?= htmlspecialchars($base) ?>/logout">Déconnexion</a>
    <?php else: ?>
      <a href="<?= htmlspecialchars($base) ?>/login">Connexion</a>
      <a href="<?= htmlspecialchars($base) ?>/register">Inscription</a>
    <?php endif; ?>
  </nav>
</header>

<main class="container">
  <?php require VIEW_PATH . '/partials/flash.php'; ?>
  <?= $content ?? '' ?>
</main>

<script src="<?= htmlspecialchars($base) ?>/assets/js/app.js" defer></script>
</body>
</html>