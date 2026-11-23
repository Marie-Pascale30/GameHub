// Appliqué avant le rendu pour éviter un flash de thème. Sombre par défaut.
(function () {
  try {
    var t = localStorage.getItem('gamehub-theme');
    if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t);
  } catch (e) { /* stockage indisponible : thème par défaut */ }
  document.documentElement.classList.add('js');
})();
