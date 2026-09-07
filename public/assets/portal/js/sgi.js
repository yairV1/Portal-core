  /* kicker/titulo/desc/kpis/areas ya los renderiza Sistema_Integral.php
     con datos reales desde $pdo (ver PortalController.php) — acá solo
     queda lo que todavía no tiene tabla real (docs/responsables/software,
     ver plan). */
  const MODULO = {
    docs: [
      { nombre: 'Mapa de Procesos Institucional', tipo: 'Caracterización', version: 'V3.0', fecha: '21 jul 2026' },
      { nombre: 'Procedimiento de Control Documental', tipo: 'Procedimiento', version: 'V2.2', fecha: '10 jul 2026' },
      { nombre: 'Matriz de Riesgos Institucional', tipo: 'Formato', version: 'V4.0', fecha: '05 jul 2026' },
      { nombre: 'Programa Anual de Auditorías', tipo: 'Plan', version: 'V1.0', fecha: '15 feb 2026' }
    ],
    responsables: [
      { ini: 'AC', nombre: 'Andrés Castaño', cargo: 'Líder SGI' },
      { ini: 'MR', nombre: 'Mónica Ríos', cargo: 'Auditora interna' }
    ],
    software: ['Suite ISO', 'Mesa de ayuda', 'Power BI · Riesgos']
  };

  document.querySelectorAll('.modulo-kpi .spark[data-valor]').forEach(el => {
    window.sparkline(el, window.tendenciaSintetica(el.dataset.valor));
  });

  document.getElementById('moduloDocs').innerHTML = MODULO.docs.map(d => `
    <tr>
      <td><strong>${d.nombre}</strong></td>
      <td style="opacity:.7">${d.tipo}</td>
      <td><span class="tag">${d.version}</span></td>
      <td style="opacity:.7">${d.fecha}</td>
    </tr>`).join('');

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
