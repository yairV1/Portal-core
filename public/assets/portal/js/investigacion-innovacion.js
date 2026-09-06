  /* kicker/titulo/desc/kpis/areas ya los renderiza Investigacion.php con
     datos reales desde $pdo (ver PortalController.php) — acá solo queda
     lo que todavía no tiene tabla real (docs/responsables/software, ver
     plan). */
  const MODULO = {
    docs: [
      { nombre: 'Política de Investigación', tipo: 'Política', version: 'V2.0', fecha: '17 jun 2026' },
      { nombre: 'Formato de Presentación de Proyectos', tipo: 'Formato', version: 'V1.4', fecha: '03 jun 2026' },
      { nombre: 'Informe de Proyección Social 2025', tipo: 'Informe', version: 'V1.0', fecha: '26 feb 2026' }
    ],
    responsables: [
      { ini: 'RO', nombre: 'Ricardo Osorio', cargo: 'Director de Investigación' },
      { ini: 'NP', nombre: 'Natalia Peña', cargo: 'Coordinadora de Egresados' }
    ],
    software: ['CRM Institucional', 'Repositorio de investigación', 'Power BI · Proyectos']
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
