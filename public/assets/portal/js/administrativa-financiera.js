  /* kicker/titulo/desc/kpis/areas ya los renderiza Financiera.php con
     datos reales desde $pdo (ver PortalController.php) — acá solo queda
     lo que todavía no tiene tabla real (docs/responsables/software, ver
     plan). */
  const MODULO = {
    docs: [
      { nombre: 'Estados Financieros 2025 (auditados)', tipo: 'Informe', version: 'V1.0', fecha: '31 mar 2026' },
      { nombre: 'Presupuesto Institucional 2026', tipo: 'Plan', version: 'V2.0', fecha: '15 ene 2026' },
      { nombre: 'Procedimiento de Compras y Contratación', tipo: 'Procedimiento', version: 'V3.1', fecha: '22 jun 2026' },
      { nombre: 'Política de Cartera y Cobranza', tipo: 'Política', version: 'V1.2', fecha: '09 may 2026' }
    ],
    responsables: [
      { ini: 'JB', nombre: 'Jorge Bermúdez', cargo: 'Director Administrativo y Financiero' },
      { ini: 'SC', nombre: 'Sandra Cárdenas', cargo: 'Contadora General' },
      { ini: 'DV', nombre: 'Diego Valencia', cargo: 'Tesorería y Cartera' }
    ],
    software: ['Software Contable', 'ERP Institucional', 'Power BI · Financiero']
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
