<?php

/** @var array $stamps */

use App\Core\CsrfToken;

$base = \App\Core\Config::get('app.base_url');
?>
<div class="stamps-page">
    <div class="stamps-container">
        <section class="stamps-list">
            <header class="stamps-list__header">
                <div class="header-content">
                    <div class="title-section">
                        <h1 class="page-title">Gestion des timbres</h1>
                        <p class="page-subtitle">Gérez votre collection de timbres et créez de nouvelles enchères</p>
                    </div>
                    <a href="<?= $base ?>/stamps/create" class="button button--primary">
                        <span class="button__icon">➕</span>
                        Ajouter un timbre
                    </a>
                </div>
            </header>

            <?php if (empty($stamps)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon">🏷️</div>
                    <h2 class="empty-state__title">Aucun timbre trouvé</h2>
                    <p class="empty-state__description">Commencez par ajouter votre premier timbre à la collection.</p>
                    <a href="<?= $base ?>/stamps/create" class="button button--primary button--large">
                        <span class="button__icon">➕</span>
                        Ajouter un timbre
                    </a>
                </div>
            <?php else: ?>
                <div class="stamps-grid">
                    <?php foreach ($stamps as $stamp): ?>
                        <article class="stamp-card">
                            <div class="stamp-card__image">
                                <?php if (!empty($stamp['main_image'])): ?>
                                    <img src="<?= htmlspecialchars($stamp['main_image']) ?>"
                                        alt="<?= htmlspecialchars($stamp['name']) ?>"
                                        loading="lazy">
                                <?php else: ?>
                                    <div class="stamp-card__placeholder">
                                        <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="stamp-card__content">
                                <h3 class="stamp-card__title">
                                    <a href="<?= $base ?>/stamps/show?id=<?= $stamp['id'] ?>">
                                        <?= htmlspecialchars($stamp['name']) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($stamp['country_name'])): ?>
                                    <p class="stamp-card__country">
                                        <span class="flag-icon flag-icon-<?= strtolower($stamp['country_code']) ?>"></span>
                                        <?= htmlspecialchars($stamp['country_name']) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="stamp-card__actions">
                                    <?php
                                    // Only show action buttons if user is logged in and owns this stamp
                                    if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $stamp['user_id']):
                                    ?>
                                        <a href="<?= $base ?>/stamps/edit?id=<?= $stamp['id'] ?>"
                                            class="button button--small button--secondary">
                                            <span class="button__icon">✏️</span>
                                            Modifier
                                        </a>
                                        <form action="<?= $base ?>/stamps/delete" method="post"
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce timbre ? Cette action est irréversible.');"
                                            style="margin:0;">
                                            <input type="hidden" name="id" value="<?= (int)$stamp['id'] ?>">
                                            <input type="hidden" name="_token" value="<?= \App\Core\CsrfToken::token() ?>">
                                            <button type="submit" class="button button--small button--danger">
                                                <span class="button__icon">🗑️</span>
                                                Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- CSS styles moved to ressources/scss/pages/_stamp.scss -->