// /frontend/assets/js/app.js
(function () {
  if (window.__APP_JS_LOADED__) return;
  window.__APP_JS_LOADED__ = true;

  // ---- CONFIG ----
  const BASE_API =
    (window.APP && window.APP.BASE_API) ||
    `${location.origin}/campus-study-room-reservation/backend/public/api`;

  // ---- UTILITIES ----
  const isObj = v => v && typeof v === 'object' && !Array.isArray(v);

  const toQuery = obj => {
    const p = new URLSearchParams();
    Object.entries(obj || {}).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      const s = String(v).trim();
      if (s !== '') p.set(k, s);
    });
    const q = p.toString();
    return q ? `?${q}` : '';
  };

  function htmlescape(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function showToast(msg = 'Saved!') {
    const t = document.getElementById('toast');
    if (!t) { alert(msg); return; }
    t.textContent = msg;
    t.removeAttribute('hidden');
    setTimeout(() => t.setAttribute('hidden', ''), 2000);
  }

  // Normalize backend "equipment" into array
  function normEquip(v) {
    if (Array.isArray(v)) return v;
    if (!v) return [];
    return String(v).split(',').map(x => x.trim()).filter(Boolean);
  }

  // ---- API HELPER ----
  async function api(path, { method = 'GET', headers = {}, body, params } = {}) {
    let url = path.startsWith('http') ? path : `${BASE_API}${path}`;
    if (params && isObj(params)) url += toQuery(params);

    const hdrs = { 'Content-Type': 'application/json', ...headers };
    const init = { method, headers: hdrs, credentials: 'include' };

    if (body !== undefined && body !== null) {
      if (body instanceof FormData) {
        delete hdrs['Content-Type'];
        init.body = body;
      } else {
        init.body = JSON.stringify(body);
      }
    }

    const res = await fetch(url, init);
    const ct = res.headers.get('content-type') || '';
    let data = ct.includes('application/json')
      ? await res.json().catch(() => null)
      : await res.text();

    if (!res.ok) {
      const msg = (isObj(data) && (data.error || data.message)) || String(data);
      throw new Error(msg || `${res.status} ${res.statusText}`);
    }
    return data;
  }

  // ---- NAVBAR: PROFILE DROPDOWN ----
  function bindProfileDropdown() {
    const root = document.getElementById('profileRoot');
    const btn  = document.getElementById('profileBtn');
    const menu = document.getElementById('profileMenu');
    if (!root || !btn || !menu) return;

    function open() {
      root.classList.add('open');
      menu.hidden = false;
      btn.setAttribute('aria-expanded', 'true');
    }
    function close() {
      root.classList.remove('open');
      menu.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
    }
    function toggle(e) {
      e.stopPropagation();
      if (menu.hidden) open(); else close();
    }
    btn.addEventListener('click', toggle);

    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });
  }

  // ---- NAVBAR: HAMBURGER / MOBILE NAV ----
  function bindHamburger() {
    const btn = document.getElementById('hamburger');
    const nav = document.getElementById('mobileNav');
    if (!btn || !nav) return;

    function open() {
      nav.hidden = false;
      btn.setAttribute('aria-expanded', 'true');
      btn.classList.add('is-open');
    }
    function close() {
      nav.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
      btn.classList.remove('is-open');
    }
    btn.addEventListener('click', () => (nav.hidden ? open() : close()));

    // close when clicking a link
    nav.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (a) close();
    });
  }

  // ---- INIT ----
  function init() {
    bindProfileDropdown();
    bindHamburger();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // ---- EXPORT (global) ----
  window.api = api;
  window.showToast = showToast;
  window.htmlescape = htmlescape;
  window.normEquip = normEquip;
  window.APP = Object.assign({ BASE_API }, window.APP || {});
})();
