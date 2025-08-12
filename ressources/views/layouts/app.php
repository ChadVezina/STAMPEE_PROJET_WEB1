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
  <header>
    <nav>
      <a href="<?= htmlspecialchars($basePath) ?>/home">Accueil</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= htmlspecialchars($basePath) ?>/dashboard">Tableau de bord</a>
        <a href="<?= htmlspecialchars($basePath) ?>/logout">Déconnexion</a>
      <?php else: ?>
        <a href="<?= htmlspecialchars($basePath) ?>/login">Connexion</a>
        <a href="<?= htmlspecialchars($basePath) ?>/register">Inscription</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
    <?php require VIEW_PATH . '/partials/flash.php'; ?>
    <?= $content ?? '' ?>
  </main>

  <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/app.js" defer></script>
</body>

</html>