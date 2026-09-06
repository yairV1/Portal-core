  // Saludo según la hora del día
  (function () {
    const el = document.getElementById('saludoKicker');
    if (!el) return;
    const hora = new Date().getHours();
    el.textContent = (hora >= 5 && hora < 12) ? 'Buenos días'
      : (hora >= 12 && hora < 19) ? 'Buenas tardes'
      : 'Buenas noches';
  })();

  // El HTML de Inicio ya lo renderiza Inicio.php con datos reales desde
  // $pdo (ver HomeController.php) — acá solo se dibuja la tendencia de
  // cada KPI, que sí necesita JS porque es un SVG calculado, no contenido.
  document.querySelectorAll('.kpi-spark[data-tendencia]').forEach(function (el) {
    const valores = el.dataset.tendencia.split(',').map(Number);
    window.sparkline(el, valores, { color: el.dataset.color, width: 60, height: 22, fill: true });
  });
