/* =====================================================================
   app.js — centralized frontend helpers
   Vanilla JS. Import CSS so Vite bundles it, then expose a small API on
   window.App for Blade views to use (http, toast, loading, modal, forms).
   ===================================================================== */

// CSS is loaded as a render-blocking <link> via @vite(['resources/css/main.css'])
// in <head>, not imported here — that avoids the flash of unstyled content
// on load that JS-injected styles cause.
import * as bootstrap from 'bootstrap';

/* ---- CSRF ----------------------------------------------------------- */
function csrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

/* ---- HTTP helpers --------------------------------------------------- */
async function request(method, url, body = null, opts = {}) {
  const headers = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken(),
    ...(opts.headers || {}),
  };

  let payload;
  if (body instanceof FormData) {
    payload = body;                         // let the browser set the boundary
  } else if (body !== null) {
    headers['Content-Type'] = 'application/json';
    payload = JSON.stringify(body);
  }

  const res = await fetch(url, {
    method,
    headers,
    body: method === 'GET' ? undefined : payload,
    credentials: 'same-origin',
  });

  const isJson = (res.headers.get('content-type') || '').includes('application/json');
  const data = isJson ? await res.json().catch(() => null) : await res.text();

  if (!res.ok) {
    const err = new Error((data && data.message) || `Request failed (${res.status})`);
    err.status = res.status;
    err.data = data;
    throw err;
  }
  return data;
}

const http = {
  get:    (url, opts)       => request('GET', url, null, opts),
  post:   (url, body, opts) => request('POST', url, body, opts),
  put:    (url, body, opts) => request('PUT', url, body, opts),
  patch:  (url, body, opts) => request('PATCH', url, body, opts),
  delete: (url, body, opts) => request('DELETE', url, body, opts),
};

/* ---- Toast / alert notifications ------------------------------------ */
const ICONS = {
  success: 'fa-circle-check',
  error:   'fa-circle-exclamation',
  warning: 'fa-triangle-exclamation',
  info:    'fa-circle-info',
};

function ensureToastStack() {
  let stack = document.querySelector('.toast-stack');
  if (!stack) {
    stack = document.createElement('div');
    stack.className = 'toast-stack';
    document.body.appendChild(stack);
  }
  return stack;
}

function toast(message, type = 'info', timeout = 4000) {
  const stack = ensureToastStack();
  const item = document.createElement('div');
  item.className = `toast-item --${type}`;
  item.innerHTML = `
    <span class="toast-ico"><i class="fa-solid ${ICONS[type] || ICONS.info}"></i></span>
    <span class="toast-msg"></span>`;
  item.querySelector('.toast-msg').textContent = message;
  stack.appendChild(item);
  if (timeout) {
    setTimeout(() => {
      item.style.opacity = '0';
      item.style.transform = 'translateY(-6px)';
      setTimeout(() => item.remove(), 200);
    }, timeout);
  }
  return item;
}

/* ---- Loading states ------------------------------------------------- */
function ensureOverlay() {
  let ov = document.querySelector('.loading-overlay');
  if (!ov) {
    ov = document.createElement('div');
    ov.className = 'loading-overlay';
    ov.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(ov);
  }
  return ov;
}

const loading = {
  show() { ensureOverlay().classList.add('--on'); },
  hide() { ensureOverlay().classList.remove('--on'); },
  // Scope a loading state to a single element (e.g. a button) during an async op
  async wrap(el, fn) {
    if (el) el.classList.add('is-loading');
    try { return await fn(); }
    finally { if (el) el.classList.remove('is-loading'); }
  },
};

/* ---- Modal helpers (Bootstrap wrapper) ------------------------------ */
const modal = {
  show(selector) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return null;
    const m = bootstrap.Modal.getOrCreateInstance(el);
    m.show();
    return m;
  },
  hide(selector) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;
    bootstrap.Modal.getOrCreateInstance(el).hide();
  },
};

/* ---- Form serialization -------------------------------------------- */
// Returns a plain object; repeated names (name="x[]") collapse into arrays.
function serialize(form) {
  const el = typeof form === 'string' ? document.querySelector(form) : form;
  const out = {};
  if (!el) return out;
  new FormData(el).forEach((value, key) => {
    if (key in out) {
      if (!Array.isArray(out[key])) out[key] = [out[key]];
      out[key].push(value);
    } else {
      out[key] = value;
    }
  });
  return out;
}

/* ---- Theme toggle (light/dark) -------------------------------------- */
// localStorage is the source of truth (read by the inline <head> script
// before first paint); the cookie mirror lets the server render the right
// <html data-theme> on the next navigation so there's no flash.
function setCookie(name, value, days = 365) {
  const exp = new Date(Date.now() + days * 864e5).toUTCString();
  document.cookie = `${name}=${value}; expires=${exp}; path=/; SameSite=Lax`;
}
const theme = {
  get() { return document.documentElement.getAttribute('data-theme') || 'light'; },
  set(mode) {
    const m = mode === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', m);
    try { localStorage.setItem('theme', m); } catch (e) { /* ignore */ }
    setCookie('theme', m);
    // Update the iOS/browser toolbar tint live (no reload needed).
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', m === 'dark' ? '#0f1115' : '#f7f8fa');
  },
  toggle() { this.set(this.get() === 'dark' ? 'light' : 'dark'); },
};

/* ---- Sidebar (mobile) ----------------------------------------------- */
function initSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const toggle = document.getElementById('sidebarToggle');
  if (!sidebar || !toggle) return;
  let backdrop = document.querySelector('.sidebar-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }
  const close = () => { sidebar.classList.remove('--open'); backdrop.classList.remove('--on'); };
  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('--open');
    backdrop.classList.toggle('--on');
  });
  backdrop.addEventListener('click', close);
}

/* ---- Flash messages from server (data-flash on <body>) -------------- */
function initServerFlash() {
  document.querySelectorAll('[data-flash]').forEach((node) => {
    const type = node.getAttribute('data-flash') || 'info';
    const msg = node.getAttribute('data-flash-msg') || node.textContent.trim();
    if (msg) toast(msg, type);
    node.remove();
  });
}

/* ---- Boot ----------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initServerFlash();
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => theme.toggle());
  });
});

/* ---- Public API ----------------------------------------------------- */
window.App = { http, toast, loading, modal, serialize, theme, csrfToken };
window.bootstrap = bootstrap;

export { http, toast, loading, modal, serialize, theme };
