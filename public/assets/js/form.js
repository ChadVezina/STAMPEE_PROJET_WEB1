(function () {
  'use strict';

  // Toggle password visibility
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-password="toggle"]');
    if (!btn) return;
    const wrap = btn.closest('.input-password');
    const input = wrap ? wrap.querySelector('[data-password="input"]') : null;
    if (!input) return;
    if (input.type === 'password') {
      input.type = 'text';
      btn.setAttribute('aria-label', 'Masquer le mot de passe');
      btn.classList.add('is-on');
    } else {
      input.type = 'password';
      btn.setAttribute('aria-label', 'Afficher le mot de passe');
      btn.classList.remove('is-on');
    }
  });

  // Double vérification (confirmations)
  function bindConfirmPair(form, sourceSel, confirmSel, errorSel) {
    const src = form.querySelector(sourceSel);
    const conf = form.querySelector(confirmSel);
    const err = errorSel ? form.querySelector(errorSel) : null;
    if (!src || !conf) return;

    const validate = () => {
      const ok = src.value.trim() !== '' && src.value === conf.value;
      conf.classList.toggle('input--error', !ok && conf.value.length > 0);
      if (err) {
        err.style.display = (!ok && conf.value.length > 0) ? 'block' : 'none';
      }
      return ok;
    };
    conf.addEventListener('input', validate);
    src.addEventListener('input', validate);

    form.addEventListener('submit', (ev) => {
      if (!validate()) {
        ev.preventDefault();
        conf.focus();
      }
    });
  }

  document.querySelectorAll('form[data-confirm="password"]').forEach(function (f) {
    bindConfirmPair(f, 'input[name="new_password"]', 'input[name="confirm_password"]', '.js-error-confirm-password');
  });

  document.querySelectorAll('form[data-confirm="email"]').forEach(function (f) {
    bindConfirmPair(f, 'input[name="email"]', 'input[name="confirm_email"]', '.js-error-confirm-email');
  });

  // Sécurité "SUPPRIMER" pour suppression
  document.querySelectorAll('form[data-guard="delete"]').forEach(function (f) {
    const guard = f.querySelector('input[name="confirm_phrase"]');
    f.addEventListener('submit', (ev) => {
      if (!guard || guard.value.trim().toUpperCase() !== 'SUPPRIMER') {
        ev.preventDefault();
        alert('Veuillez saisir correctement le mot "SUPPRIMER".');
        guard && guard.focus();
      }
    });
  });
})();
