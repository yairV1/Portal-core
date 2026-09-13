// doc-modal.js — pestañas dentro de un <dialog> de "centro documental" (ver
// _explorador_documental.php: un modal, 3 acciones — Carpeta/Archivo/Drive).
// No hace nada en páginas sin [data-modal-tabs] (ej. _agregar_documento.php
// no lo necesita: sus 2 "pestañas" son solo radios que cambian un campo
// oculto, mismo formulario, sin secciones que mostrar/ocultar).
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-modal-tabs]').forEach(function (tabs) {
    var dialog = tabs.closest('dialog');
    if (!dialog) return;
    tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        tabs.querySelectorAll('[data-tab]').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        dialog.querySelectorAll('[data-section]').forEach(function (seccion) {
          seccion.hidden = seccion.dataset.section !== btn.dataset.tab;
        });
      });
    });
  });
});
