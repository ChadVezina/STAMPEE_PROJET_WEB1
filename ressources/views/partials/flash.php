<?php if (!empty($flash['error'])): ?>
  <div class="alert alert-error" data-alert="error">
    <?= htmlspecialchars($flash['error']) ?>
    <button type="button" class="close-btn" data-dismiss="alert">&times;</button>
  </div>
<?php endif; ?>
<?php if (!empty($flash['success'])): ?>
  <div class="alert alert-success" data-alert="success">
    <?= htmlspecialchars($flash['success']) ?>
    <button type="button" class="close-btn" data-dismiss="alert">&times;</button>
  </div>
<?php endif; ?>