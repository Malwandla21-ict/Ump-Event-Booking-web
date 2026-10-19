/**
 * Progressive enhancement only — every page works without JavaScript.
 */
(() => {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  /* ---------- Theme toggle ---------- */
  const systemDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;
  const currentTheme = () => document.documentElement.getAttribute('data-theme') || (systemDark() ? 'dark' : 'light');

  $$('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = currentTheme() === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('theme', next); } catch (e) { /* ignore */ }
    });
  });

  /* ---------- Mobile nav ---------- */
  const navToggle = $('[data-nav-toggle]');
  const nav = $('#site-nav');
  if (navToggle && nav) {
    const setOpen = (open) => {
      nav.classList.toggle('is-open', open);
      navToggle.setAttribute('aria-expanded', String(open));
    };
    navToggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && setOpen(false));
  }

  /* ---------- Admin sidebar ---------- */
  const sidebar = $('#admin-sidebar');
  const backdrop = $('[data-sidebar-close]');
  const sidebarToggle = $('[data-sidebar-toggle]');
  if (sidebar && sidebarToggle) {
    const setOpen = (open) => {
      sidebar.classList.toggle('is-open', open);
      sidebarToggle.setAttribute('aria-expanded', String(open));
      if (backdrop) backdrop.hidden = !open;
    };
    sidebarToggle.addEventListener('click', () => setOpen(true));
    backdrop?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && setOpen(false));
  }

  /* ---------- User menu: close on outside click ---------- */
  document.addEventListener('click', (e) => {
    $$('details.user-menu[open]').forEach((d) => { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  /* ---------- Toasts ---------- */
  const dismiss = (toast) => {
    toast.classList.add('is-leaving');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
  };
  $$('[data-toast]').forEach((toast) => {
    $('[data-toast-close]', toast)?.addEventListener('click', () => dismiss(toast));
    const isError = toast.classList.contains('toast-error');
    if (!isError) setTimeout(() => dismiss(toast), 6000);
  });

  /* ---------- Capacity meters (width set here to keep CSP free of inline styles) ---------- */
  requestAnimationFrame(() => {
    $$('[data-fill]').forEach((el) => { el.style.width = `${Math.min(100, Number(el.dataset.fill) || 0)}%`; });
  });

  /* ---------- Show / hide password ---------- */
  $$('[data-reveal]').forEach((btn) => {
    const input = document.getElementById(btn.dataset.reveal);
    if (!input) return;
    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      $('use', btn)?.setAttribute('href', show ? '#i-eye-off' : '#i-eye');
    });
  });

  /* ---------- Password strength ---------- */
  $$('[data-strength]').forEach((input) => {
    const meter = document.getElementById(input.dataset.strength);
    if (!meter) return;
    input.addEventListener('input', () => {
      const v = input.value;
      let score = 0;
      if (v.length >= 8) score++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
      if (/\d/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v) || v.length >= 14) score++;
      meter.dataset.score = v ? String(Math.max(1, score)) : '0';
    });
  });

  /* ---------- Quantity stepper ---------- */
  $$('[data-stepper]').forEach((stepper) => {
    const input = $('input', stepper);
    const [dec, inc] = $$('[data-step]', stepper);
    const min = Number(input.min) || 1;
    const max = Number(input.max) || 1;
    const sync = () => {
      const v = Math.min(max, Math.max(min, Number(input.value) || min));
      input.value = v;
      dec.disabled = v <= min;
      inc.disabled = v >= max;
    };
    $$('[data-step]', stepper).forEach((btn) => btn.addEventListener('click', () => {
      input.value = Number(input.value) + Number(btn.dataset.step);
      sync();
    }));
    input.addEventListener('change', sync);
    sync();
  });

  /* ---------- Confirm dialogs for destructive forms ---------- */
  const dialog = $('#confirm-dialog');
  $$('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (form.dataset.confirmed === '1') return;
      e.preventDefault();

      if (!dialog || typeof dialog.showModal !== 'function') {
        if (window.confirm(form.dataset.confirm)) { form.dataset.confirmed = '1'; form.requestSubmit(); }
        return;
      }

      $('[data-confirm-title]', dialog).textContent = form.dataset.confirmTitle || 'Are you sure?';
      $('[data-confirm-message]', dialog).textContent = form.dataset.confirm;
      $('[data-confirm-ok]', dialog).textContent = form.dataset.confirmOk || 'Confirm';
      dialog.returnValue = '';
      dialog.showModal();
      dialog.addEventListener('close', () => {
        if (dialog.returnValue === 'confirm') {
          form.dataset.confirmed = '1';
          form.requestSubmit();
        }
      }, { once: true });
    });
  });

  /* ---------- Prevent double submits + loading state ---------- */
  $$('form[data-loading-form], form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (e.defaultPrevented) return;
      const btn = e.submitter || $('button[type="submit"]', form);
      if (form.dataset.submitting === '1') { e.preventDefault(); return; }
      form.dataset.submitting = '1';
      btn?.classList.add('is-loading');
      btn?.setAttribute('aria-busy', 'true');
    });
  });
  // Restore buttons when navigating back (bfcache).
  window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    $$('form[data-submitting]').forEach((f) => { delete f.dataset.submitting; delete f.dataset.confirmed; });
    $$('.is-loading').forEach((b) => b.classList.remove('is-loading'));
  });

  /* ---------- Image upload preview + drag & drop ---------- */
  const fileInput = $('[data-image-input]');
  const preview = $('[data-image-preview]');
  const dropzone = $('[data-dropzone]');
  if (fileInput && preview) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files?.[0];
      if (!file) return;
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
        alert('Please choose a JPG, PNG or WebP image.');
        fileInput.value = '';
        return;
      }
      $('img', preview).src = URL.createObjectURL(file);
      preview.hidden = false;
    });
    ['dragenter', 'dragover'].forEach((t) => dropzone?.addEventListener(t, () => dropzone.classList.add('is-dragover')));
    ['dragleave', 'drop'].forEach((t) => dropzone?.addEventListener(t, () => dropzone.classList.remove('is-dragover')));
  }

  /* ---------- Print ticket ---------- */
  $$('[data-print]').forEach((btn) => btn.addEventListener('click', () => window.print()));
})();
