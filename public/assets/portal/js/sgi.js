  /* ═══ Datos de ejemplo — Sistema de Gestión Integral ═══ */
  const MODULO = {
    kicker: 'Sistema de Gestión Integral',
    titulo: 'Procesos, riesgos y mejoramiento',
    desc: 'Mapa de procesos, caracterizaciones, procedimientos, auditorías, riesgos y planes de mejoramiento bajo ISO 9001.',
    kpis: [
      { label: 'Procesos', valor: '9' },
      { label: 'Procedimientos', valor: '87' },
      { label: 'Riesgos activos', valor: '31' },
      { label: 'Hallazgos abiertos', valor: '6' }
    ],
    areas: [
      { label: 'Mapa de procesos', meta: 'Estratégicos · Misionales · Apoyo · Evaluación' },
      { label: 'Caracterizaciones', meta: '9 procesos con entradas y salidas' },
      { label: 'Auditorías internas', meta: 'Ciclo 2026 · 4 auditorías' },
      { label: 'Riesgos y oportunidades', meta: 'Matriz institucional' }
    ],
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

  document.getElementById('moduloKicker').textContent = MODULO.kicker;
  document.getElementById('moduloTitulo').textContent = MODULO.titulo;
  document.getElementById('moduloDesc').textContent = MODULO.desc;

  document.getElementById('moduloKpis').innerHTML = MODULO.kpis.map(k => `
    <div class="modulo-kpi"><div class="label">${k.label}</div><div class="valor">${k.valor}</div></div>`).join('');

  document.getElementById('moduloAreas').innerHTML = MODULO.areas.map(a => `
    <div class="area-item">
      <span class="ic"><i class="bi bi-folder2"></i></span>
      <span style="flex:1">
        <span class="label">${a.label}</span>
        <span class="meta">${a.meta}</span>
      </span>
      <span class="arrow">→</span>
    </div>`).join('');

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
