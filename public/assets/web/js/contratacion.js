(function () {
  var form = document.getElementById('ctForm');
  if (!form) return; // ya completado/expirado — no hay formulario en la página

  var steps = Array.prototype.slice.call(form.querySelectorAll('.ct-step'));
  var total = window.CT_TOTAL_PASOS || steps.length;
  var titulos = window.CT_TITULOS_PASOS || [];
  var actual = 1;

  var btnAnterior = document.getElementById('ctBtnAnterior');
  var btnSiguiente = document.getElementById('ctBtnSiguiente');
  var btnEnviar = document.getElementById('ctBtnEnviar');
  var elPasoActual = document.getElementById('ctPasoActual');
  var elPasoTitulo = document.getElementById('ctPasoTitulo');
  var elFill = document.getElementById('ctProgresoFill');

  function pasoPorNumero(n) {
    return steps.filter(function (s) { return Number(s.dataset.paso) === n; })[0];
  }

  // Solo valida los campos del paso VISIBLE — si no, el navegador reclama
  // por un campo obligatorio de un paso que el usuario todavía no ve.
  function pasoValido(n) {
    var campos = pasoPorNumero(n).querySelectorAll('[required]');
    for (var i = 0; i < campos.length; i++) {
      if (!campos[i].checkValidity()) {
        campos[i].reportValidity();
        return false;
      }
    }
    return true;
  }

  function actualizarProgreso() {
    elPasoActual.textContent = actual;
    elPasoTitulo.textContent = titulos[actual - 1] || '';
    elFill.style.width = (actual / total * 100) + '%';
    btnAnterior.hidden = actual === 1;
    btnSiguiente.hidden = actual === total;
    btnEnviar.hidden = actual !== total;
  }

  function irA(n, direccion) {
    var pasoViejo = pasoPorNumero(actual);
    var pasoNuevo = pasoPorNumero(n);

    function mostrarNuevo() {
      pasoViejo.hidden = true;
      pasoNuevo.hidden = false;
      actual = n;
      actualizarProgreso();
      if (window.gsap) {
        gsap.fromTo(pasoNuevo, { opacity: 0, x: direccion * 24 }, { opacity: 1, x: 0, duration: .35, ease: 'power2.out' });
      }
      window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
    }

    if (window.gsap) {
      gsap.to(pasoViejo, { opacity: 0, x: direccion * -24, duration: .22, ease: 'power2.in', onComplete: mostrarNuevo });
    } else {
      mostrarNuevo();
    }
  }

  btnSiguiente.addEventListener('click', function () {
    if (!pasoValido(actual)) return;
    if (actual < total) irA(actual + 1, 1);
  });
  btnAnterior.addEventListener('click', function () {
    if (actual > 1) irA(actual - 1, -1);
  });

  // Con hasta 18 archivos, el envío puede tardar unos segundos — sin esto,
  // un doble clic en "Enviar" manda el formulario dos veces.
  form.addEventListener('submit', function (e) {
    if (!pasoValido(total)) { e.preventDefault(); return; }
    btnEnviar.disabled = true;
    btnEnviar.textContent = 'Enviando…';
  });

  actualizarProgreso();
})();
