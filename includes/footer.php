document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Theme toggle (persisted, respects system preference) ---------- */
  var root = document.documentElement;
  var THEME_KEY = 'techtime-theme';
  var saved = localStorage.getItem(THEME_KEY);
  if (saved) {
    root.setAttribute('data-theme', saved);
  } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    root.setAttribute('data-theme', 'dark');
  }
  function currentTheme() { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; }
  function syncToggleIcon() {
    document.querySelectorAll('.theme-toggle i').forEach(function (icon) {
      icon.className = currentTheme() === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });
  }
  syncToggleIcon();
  document.querySelectorAll('.theme-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var next = currentTheme() === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem(THEME_KEY, next);
      syncToggleIcon();
    });
  });

  /* ---------- User + cart dropdowns ---------- */
  document.querySelectorAll('.user-menu-btn, .cart-menu-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var dd = btn.parentElement.querySelector('.user-dropdown, .cart-dropdown');
      document.querySelectorAll('.user-dropdown.open, .cart-dropdown.open').forEach(function (o) { if (o !== dd) o.classList.remove('open'); });
      dd.classList.toggle('open');
    });
  });
  document.addEventListener('click', function (e) {
    if (e.target.closest('.cart-dropdown')) return;
    document.querySelectorAll('.user-dropdown.open, .cart-dropdown.open').forEach(function (dd) { dd.classList.remove('open'); });
  });

  /* ---------- Review / chat tab switching ---------- */
  var tabBtns = document.querySelectorAll('.review-tab-btn');
  if (tabBtns.length) {
    tabBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tab = btn.dataset.tab;
        tabBtns.forEach(function (b) { b.classList.toggle('active', b === btn); });
        document.getElementById('panel-reviews').style.display = tab === 'reviews' ? '' : 'none';
        document.getElementById('panel-chat').style.display = tab === 'chat' ? '' : 'none';
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
      });
    });
  }

  /* ---------- Interactive star rating input (works for every .star-input on the page) ---------- */
  document.querySelectorAll('.star-input').forEach(function (starInput) {
    var stars = starInput.querySelectorAll('i');
    var hidden = starInput.parentElement.querySelector('.ratingValue');
    if (!hidden) return;
    function paint(n) {
      stars.forEach(function (s, i) {
        s.className = i < n ? 'fa-solid fa-star' : 'fa-regular fa-star';
      });
    }
    stars.forEach(function (star, idx) {
      star.addEventListener('mouseenter', function () { paint(idx + 1); });
      star.addEventListener('click', function () { hidden.value = idx + 1; });
    });
    starInput.addEventListener('mouseleave', function () {
      paint(parseInt(hidden.value || '0', 10));
    });
  });

  /* ---------- Review edit toggle ---------- */
  document.querySelectorAll('.review-edit-toggle, .review-edit-cancel').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.getElementById(btn.dataset.target);
      if (target) target.style.display = target.style.display === 'none' ? 'block' : 'none';
    });
  });

  /* ---------- Product gallery thumbnails ---------- */
  var thumbBtns = document.querySelectorAll('.product-thumb-btn');
  var mainImg = document.getElementById('mainProductImage');
  if (thumbBtns.length && mainImg) {
    thumbBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        mainImg.src = btn.dataset.img;
        thumbBtns.forEach(function (b) { b.classList.toggle('active', b === btn); });
      });
    });
  }

  /* ---------- Lightweight client-side form validation ---------- */
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var ok = true;
      form.querySelectorAll('[data-rule]').forEach(function (field) {
        var rule = field.dataset.rule;
        var errorEl = document.getElementById(field.id + 'Error');
        var msg = '';
        var val = field.value.trim();
        if (rule === 'required' && val === '') msg = 'This field is required.';
        if (rule === 'min2' && val.length < 2) msg = 'Must be at least 2 characters.';
        if (rule === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) msg = 'Enter a valid email address.';
        if (rule === 'password' && val.length < 6) msg = 'Password must be at least 6 characters.';
        if (rule === 'confirm') {
          var other = document.getElementById(field.dataset.matches);
          if (val !== other.value) msg = 'Passwords do not match.';
        }
        if (msg) { ok = false; field.style.borderColor = 'var(--danger)'; } else { field.style.borderColor = ''; }
        if (errorEl) errorEl.textContent = msg;
      });
      if (!ok) e.preventDefault();
    });
  });

  /* ---------- Cart quantity: auto-submit on change ---------- */
  document.querySelectorAll('.qty-form input[type="number"]').forEach(function (input) {
    input.addEventListener('change', function () { input.form.submit(); });
  });

  /* ---------- Admin: auto-submit status dropdowns ---------- */
  document.querySelectorAll('.status-form select').forEach(function (sel) {
    sel.addEventListener('change', function () { sel.form.submit(); });
  });

  /* ---------- Confirm before destructive admin actions ---------- */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  /* ---------- Toasts (used for quick confirmations) ---------- */
  window.showToast = function (message) {
    var stack = document.getElementById('toast-stack');
    if (!stack) return;
    var t = document.createElement('div');
    t.className = 'toast';
    t.textContent = message;
    stack.appendChild(t);
    setTimeout(function () { t.remove(); }, 3200);
  };
  var toastMsg = document.body.dataset.toast;
  if (toastMsg) window.showToast(toastMsg);
});
