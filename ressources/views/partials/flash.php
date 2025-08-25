<?php if (!empty($_SESSION['flash']['error'])): ?>
  <div class="alert alert-error" data-alert="error">
    <?= htmlspecialchars($_SESSION['flash']['error']) ?>
    <button type="button" class="close-btn" data-dismiss="alert">&times;</button>
  </div>
  <?php unset($_SESSION['flash']['error']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash']['success'])): ?>
  <div class="alert alert-success" data-alert="success">
    <?= htmlspecialchars($_SESSION['flash']['success']) ?>
    <button type="button" class="close-btn" data-dismiss="alert">&times;</button>
  </div>
  <?php unset($_SESSION['flash']['success']); ?>
<?php endif; ?>