(function () {
  const q = (sel, ctx = document) => ctx.querySelector(sel);
  const qa = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const setErr = (input, msg) => {
    const small = q(`small.error[data-for="${input.id}"]`) || q(`small.error[data-for="${input.name}"]`);
    if (small) small.textContent = msg || '';
  };
  const emailOk = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

  // Register form
  const fr = q('#form-register');
  if (fr) {
    const nom = q('#nom'), email = q('#email'), pwd = q('#password'), cfm = q('#confirm');

    const validate = () => {
      let ok = true;
      setErr(nom, nom.value.trim().length >= 2 ? '' : 'Nom trop court');
      if (nom.value.trim().length < 2) ok = false;

      setErr(email, emailOk(email.value) ? '' : 'Email invalide');
      if (!emailOk(email.value)) ok = false;

      setErr(pwd, pwd.value.length >= 8 ? '' : '8 caractères minimum');
      if (pwd.value.length < 8) ok = false;

      setErr(cfm, cfm.value === pwd.value ? '' : 'Ne correspond pas');
      if (cfm.value !== pwd.value) ok = false;

      return ok;
    };

    qa('input', fr).forEach(i => i.addEventListener('input', validate));
    fr.addEventListener('submit', (e) => { if (!validate()) e.preventDefault(); });
  }

  // Login form
  const fl = q('#form-login');
  if (fl) {
    const email = q('#email'), pwd = q('#password');
    const validate = () => {
      let ok = true;
      setErr(email, emailOk(email.value) ? '' : 'Email invalide');
      if (!emailOk(email.value)) ok = false;
      setErr(pwd, pwd.value.length >= 8 ? '' : '8 caractères minimum');
      if (pwd.value.length < 8) ok = false;
      return ok;
    };
    qa('input', fl).forEach(i => i.addEventListener('input', validate));
    fl.addEventListener('submit', (e) => { if (!validate()) e.preventDefault(); });
  }
})();