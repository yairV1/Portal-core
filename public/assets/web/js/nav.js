// nav.js — menú de la barra superior de las landings en pantallas angostas
// (≤760px: los enlaces se ocultan y el botón .menu-toggle los despliega).
(function () {
  'use strict';
  var barra = document.querySelector('.navbar');
  var boton = barra && barra.querySelector('.menu-toggle');
  var menu = barra && barra.querySelector('nav.links');
  if (!boton || !menu) return;
  function abrir(si) {
    barra.classList.toggle('is-open', si);
    boton.setAttribute('aria-expanded', si ? 'true' : 'false');
    boton.setAttribute('aria-label', si ? 'Cerrar menú' : 'Abrir menú');
  }
  boton.addEventListener('click', function () { abrir(!barra.classList.contains('is-open')); });
  menu.addEventListener('click', function (e) { if (e.target.closest('a')) abrir(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && barra.classList.contains('is-open')) { abrir(false); boton.focus(); }
  });
  window.matchMedia('(min-width: 761px)').addEventListener('change', function (m) { if (m.matches) abrir(false); });
})();
