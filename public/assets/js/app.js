/* GameHub v2 — interactions (JavaScript natif, aucune dépendance). */
'use strict';

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
const csrfToken = () => $('meta[name="csrf-token"]')?.content ?? '';

/**
 * Appel JSON vers l'API. Les requêtes d'écriture envoient le jeton CSRF
 * dans l'en-tête X-CSRF-Token (vérifié côté serveur pour tout POST).
 */
async function api(url, { method = 'GET', body } = {}) {
  const options = { method, credentials: 'same-origin', headers: { Accept: 'application/json' } };
  if (method !== 'GET') {
    options.headers['X-CSRF-Token'] = csrfToken();
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(body ?? {});
  }
  const response = await fetch(url, options);
  let data = {};
  try { data = await response.json(); } catch { /* réponse non JSON */ }

  if (response.status === 401) {
    window.location.href = '/connexion?next=' + encodeURIComponent(location.pathname + location.search);
    throw new Error('auth');
  }
  if (!response.ok || data.ok === false) {
    throw new Error(data.error || 'Une erreur est survenue, réessayez.');
  }
  return data;
}

/* ---------- Notifications discrètes (bas d'écran, disparaissent seules) ---------- */
function toast(message, type = 'success') {
  const region = $('[data-toasts]');
  if (!region) return;
  const el = document.createElement('div');
  el.className = `toast toast--${type}`;
  el.textContent = message;
  region.append(el);
  requestAnimationFrame(() => el.classList.add('is-in'));
  setTimeout(() => {
    el.classList.remove('is-in');
    setTimeout(() => el.remove(), 300);
  }, 2600);
}

/* ---------- Thème clair / sombre (préférence mémorisée) ---------- */
$('[data-theme-toggle]')?.addEventListener('click', () => {
  const root = document.documentElement;
  const next = root.dataset.theme === 'light' ? 'dark' : 'light';
  root.dataset.theme = next;
  try { localStorage.setItem('gamehub-theme', next); } catch { /* navigation privée */ }
});

/* ---------- Messages flash et confirmations ---------- */
document.addEventListener('click', (e) => {
  const close = e.target.closest('[data-dismiss]');
  if (close) close.closest('.flash')?.remove();
});

document.addEventListener('submit', (e) => {
  const form = e.target;
  if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) e.preventDefault();
}, true);

/* ---------- Coups de cœur (sans rechargement) ---------- */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-fav]');
  if (!btn) return;
  e.preventDefault();
  const id = btn.dataset.fav;
  btn.disabled = true;

  try {
    const data = await api(`/api/jeux/${id}/favori`, { method: 'POST' });
    $$(`[data-fav="${id}"]`).forEach((b) => {
      b.setAttribute('aria-pressed', String(data.favorite));
      b.classList.remove('pop');
      void b.offsetWidth;
      if (data.favorite) b.classList.add('pop');
      const label = b.getAttribute('aria-label');
      if (label) {
        b.setAttribute('aria-label', data.favorite
          ? label.replace(/^Ajouter/, 'Retirer').replace(/ à mes /, ' de mes ')
          : label.replace(/^Retirer/, 'Ajouter').replace(/ de mes /, ' à mes '));
      }
      const text = $('[data-fav-text]', b);
      if (text) text.textContent = data.favorite ? 'Dans mes coups de cœur' : 'Ajouter aux coups de cœur';
    });
    $$(`[data-fav-count="${id}"]`).forEach((c) => { c.textContent = data.count; });
    toast(data.message);

    // Page « Mes coups de cœur » : la carte retirée disparaît.
    const favPage = $('[data-fav-page]');
    if (!data.favorite && favPage) {
      const card = btn.closest('.card');
      card?.classList.add('is-leaving');
      setTimeout(() => {
        card?.remove();
        const left = $$('.card', favPage).length;
        const total = $('[data-fav-total]');
        if (total) total.textContent = left ? `${left} ${left > 1 ? 'jeux' : 'jeu'} qui vous ont marqué.` : '';
        if (!left) $('[data-fav-empty]').hidden = false;
      }, 260);
    }
  } catch (err) {
    if (err.message !== 'auth') toast(err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

/* ---------- Catalogue : recherche, filtres, tri, défilement infini ---------- */
const filters = $('[data-filters]');
if (filters) {
  const grid = $('[data-grid]');
  const more = $('[data-more]');
  const count = $('[data-results-count]');
  const empty = $('[data-empty]');
  const reset = $('[data-reset]');
  const sentinel = $('[data-sentinel]');
  let page = Number(grid.dataset.page);
  let pages = Number(grid.dataset.pages);
  let loading = false;
  let controller = null;

  const buildQuery = (p = 1) => {
    const qs = new URLSearchParams();
    for (const [key, value] of new FormData(filters)) {
      if (value !== '' && !(key === 'tri' && value === 'populaire')) qs.set(key, value);
    }
    if (p > 1) qs.set('page', String(p));
    return qs;
  };

  async function load(p, append) {
    controller?.abort();
    controller = new AbortController();
    loading = true;
    grid.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch('/api/jeux?' + buildQuery(p), {
        headers: { Accept: 'application/json' },
        signal: controller.signal,
      });
      const data = await response.json();
      if (append) grid.insertAdjacentHTML('beforeend', data.html);
      else grid.innerHTML = data.html;
      page = data.page;
      pages = data.pages;
      count.innerHTML = `<strong>${data.total}</strong> ${data.total > 1 ? 'jeux trouvés' : 'jeu trouvé'}`;
      empty.hidden = data.total !== 0;
      more.hidden = !data.hasMore;
      const qs = buildQuery(1);
      reset.hidden = ![...qs.keys()].some((k) => k !== 'tri');
      if (!append) history.replaceState(null, '', qs.toString() ? '/?' + qs : '/');
    } catch (err) {
      if (err.name !== 'AbortError') toast('Impossible de charger les jeux.', 'error');
    } finally {
      loading = false;
      grid.removeAttribute('aria-busy');
    }
  }

  let debounce;
  $('[data-search]').addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => load(1, false), 250);
  });
  filters.addEventListener('change', (e) => { if (e.target.matches('select')) load(1, false); });
  filters.addEventListener('submit', (e) => { e.preventDefault(); load(1, false); });
  more.addEventListener('click', () => { if (!loading && page < pages) load(page + 1, true); });

  if ('IntersectionObserver' in window) {
    new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting && !loading && page < pages) load(page + 1, true);
    }, { rootMargin: '300px' }).observe(sentinel);
  }
}

/* ---------- Notes et avis ---------- */
document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (!form.matches('[data-review-form], [data-review-delete]') || e.defaultPrevented) return;
  e.preventDefault();

  const body = Object.fromEntries(new FormData(form));
  delete body._csrf;
  const submit = $('button[type="submit"]', form);
  submit.disabled = true;

  try {
    const data = await api(form.action, { method: 'POST', body });
    $('[data-reviews]').innerHTML = data.html;
    const score = $('[data-score]');
    if (score && data.scoreHtml) score.innerHTML = data.scoreHtml;
    toast(data.message);
  } catch (err) {
    if (err.message !== 'auth') toast(err.message, 'error');
    submit.disabled = false;
  }
});

/* ---------- Compteur de caractères ---------- */
document.addEventListener('input', (e) => {
  if (!e.target.matches('[data-counter]')) return;
  const out = e.target.parentElement.querySelector('[data-counter-out]');
  if (out) out.textContent = `${e.target.value.length} / ${e.target.maxLength}`;
});

/* ---------- Règles de mot de passe en direct ---------- */
const PASSWORD_RULES = {
  length: (v) => v.length >= 10,
  upper: (v) => /\p{Lu}/u.test(v),
  lower: (v) => /\p{Ll}/u.test(v),
  digit: (v) => /\d/.test(v),
  special: (v) => /[^\p{L}\d]/u.test(v),
};
$$('[data-pw-rules]').forEach((list) => {
  const input = document.getElementById(list.dataset.pwRules);
  if (!input) return;
  const update = () => {
    $$('[data-rule]', list).forEach((li) => {
      li.classList.toggle('is-ok', PASSWORD_RULES[li.dataset.rule](input.value));
    });
  };
  input.addEventListener('input', update);
  update();
});

/* ---------- Comptes de démo : préremplissage ---------- */
$$('[data-fill-login]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const form = $('form[action="/connexion"]');
    form.email.value = btn.dataset.fillLogin;
    form.password.value = 'password';
    $('button[type="submit"]', form).focus();
  });
});
