<?php $u = $user ?? $_SESSION['user'] ?? null; ?>
<h1>Tableau de bord</h1>
<p>Bienvenue, <strong><?= htmlspecialchars($u['nom'] ?? 'Utilisateur'); ?></strong>.</p>