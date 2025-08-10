<?php if (!empty($flash['error'])): ?>
  <div class="alert alert-error"><?= htmlspecialchars($flash['error']) ?></div>
<?php endif; ?>
<?php if (!empty($flash['success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
<?php endif; ?>