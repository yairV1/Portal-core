(function () {
  var popover = document.getElementById('calPopover');
  if (!popover) return; // no admin — nada que enganchar

  var POP_WIDTH = 260; // mismo valor que .cal-popover en calendario.css
  var fechaInput = document.getElementById('calPopFecha');
  var mesInput = popover.querySelector('input[name="mes"]');
  var tituloInput = popover.querySelector('input[name="titulo"]');
  var mes = mesInput.value;

  function pad(n) { return String(n).padStart(2, '0'); }

  function abrir(celda) {
    var dia = celda.dataset.dia;
    var rect = celda.getBoundingClientRect();

    var left = rect.left;
    var maxLeft = window.innerWidth - POP_WIDTH - 12;
    if (left > maxLeft) left = Math.max(12, maxLeft);

    popover.style.top = (rect.bottom + 8) + 'px';
    popover.style.left = left + 'px';
    fechaInput.value = mes + '-' + pad(dia);
    popover.hidden = false;
    tituloInput.focus();
  }

  function cerrar() {
    popover.hidden = true;
  }

  document.querySelectorAll('.cal-celda[data-dia]').forEach(function (celda) {
    celda.addEventListener('click', function () { abrir(celda); });
  });

  document.getElementById('calPopClose').addEventListener('click', cerrar);

  // Clic afuera del popover (y que no sea el botón que lo abrió) lo cierra.
  document.addEventListener('click', function (e) {
    if (popover.hidden) return;
    if (popover.contains(e.target)) return;
    if (e.target.closest('.cal-celda[data-dia]')) return;
    cerrar();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrar();
  });
})();
