  /* kicker/titulo/desc/kpis/areas ya los renderiza Vicerrectoria.php con
     datos reales desde $pdo (ver PortalController.php) — acá solo queda
     lo que todavía no tiene tabla real (docs/responsables/software, ver
     plan). */
  const MODULO = {
    docs: [
      { nombre: 'Reglamento Estudiantil', tipo: 'Reglamento', version: 'V5.0', fecha: '20 jul 2026' },
      { nombre: 'Calendario Académico 2026-II', tipo: 'Cronograma', version: 'V1.1', fecha: '01 jul 2026' },
      { nombre: 'Estatuto Docente', tipo: 'Reglamento', version: 'V3.0', fecha: '14 abr 2026' },
      { nombre: 'Plan de Autoevaluación Institucional', tipo: 'Plan', version: 'V2.0', fecha: '28 may 2026' }
    ],
    responsables: [
      { ini: 'CN', nombre: 'Camilo Naranjo', cargo: 'Vicerrector Académico' },
      { ini: 'YT', nombre: 'Yolanda Torres', cargo: 'Registro y Control Académico' }
    ],
    software: ['Software Académico', 'Campus Virtual', 'Biblioteca digital']
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
