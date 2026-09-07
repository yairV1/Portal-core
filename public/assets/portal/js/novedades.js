  /* kicker/titulo/desc/kpis/areas ya los renderiza Novedades.php con
     datos reales desde $pdo (ver PortalController.php) — acá solo queda
     lo que todavía no tiene tabla real (docs/responsables/software, ver
     plan). */
  const MODULO = {
    docs: [
      { nombre: 'Circular 061 · Cierre académico 2026-II', tipo: 'Circular', version: 'V1.0', fecha: '29 jul 2026' },
      { nombre: 'Comunicado Rectoría · Acreditación', tipo: 'Comunicado', version: 'V1.0', fecha: '24 jul 2026' },
      { nombre: 'Boletín Institucional Julio', tipo: 'Boletín', version: 'V1.0', fecha: '18 jul 2026' }
    ],
    responsables: [
      { ini: 'VS', nombre: 'Valentina Suárez', cargo: 'Comunicaciones' }
    ],
    software: ['Correo institucional', 'Google Workspace']
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
