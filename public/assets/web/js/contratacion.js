(function () {
  var form = document.querySelector('.ct-form');
  if (!form) return;

  // Con hasta 18 archivos, el envío puede tardar unos segundos — sin esto,
  // un doble clic en "Enviar" manda el formulario dos veces.
  form.addEventListener('submit', function () {
    var btn = form.querySelector('button[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Enviando…';
    }
  });
})();
