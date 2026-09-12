  /* kicker/titulo/desc/kpis/areas/docs/responsables/software ya los
     renderiza la vista PHP con datos reales desde $pdo (ver
     PortalController.php) — acá solo queda lo que de verdad necesita JS. */
  document.querySelectorAll('.modulo-kpi .spark[data-valor]').forEach(el => {
    window.sparkline(el, window.tendenciaSintetica(el.dataset.valor));
  });
