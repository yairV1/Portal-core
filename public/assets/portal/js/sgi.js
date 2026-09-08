  /* kicker/titulo/desc/kpis/areas/docs ya los renderiza Sistema_Integral.php
     con datos reales desde $pdo (ver PortalController.php) — acá solo
     queda lo que todavía no tiene tabla real (responsables/software, ver
     plan). */
  const MODULO = {
    responsables: [
      { ini: 'AC', nombre: 'Andrés Castaño', cargo: 'Líder SGI' },
      { ini: 'MR', nombre: 'Mónica Ríos', cargo: 'Auditora interna' }
    ],
    software: ['Suite ISO', 'Mesa de ayuda', 'Power BI · Riesgos']
  };

  document.querySelectorAll('.modulo-kpi .spark[data-valor]').forEach(el => {
    window.sparkline(el, window.tendenciaSintetica(el.dataset.valor));
  });

  document.getElementById('moduloResponsables').innerHTML = MODULO.responsables.map(r => `
    <div class="responsable">
      <span class="ini">${r.ini}</span>
      <span style="flex:1">
        <span class="nombre">${r.nombre}</span>
        <span class="cargo">${r.cargo}</span>
      </span>
    </div>`).join('');

  document.getElementById('moduloSoftware').innerHTML = MODULO.software.map(s => `
    <div class="software-item"><span style="opacity:.6"><i class="bi bi-link-45deg"></i></span>${s}</div>`).join('');
