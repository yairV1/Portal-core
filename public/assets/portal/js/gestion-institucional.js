  /* ═══ Datos de ejemplo — Gestión Institucional ═══ */
  const MODULO = {
    kicker: 'Dirección de Planeación Estratégica y Gestión Humana',
    titulo: 'Gestión Institucional',
    desc: 'Planeación estratégica, PDI, PEI, políticas y planes de acción. El norte institucional y su despliegue a cada área.',
    kpis: [
      { label: 'Avance PDI', valor: '62%' },
      { label: 'Planes de acción', valor: '11' },
      { label: 'Políticas vigentes', valor: '24' },
      { label: 'Metas 2026', valor: '48' }
    ],
    areas: [
      { label: 'Planeación Estratégica', meta: 'PDI 2025-2030 · PEI · Mapa estratégico' },
      { label: 'Sistema de Gestión Integral', meta: '9 procesos caracterizados · ISO 9001' },
      { label: 'Gestión Humana y Desarrollo Organizacional', meta: 'Manual de funciones · Desempeño' },
      { label: 'Bienestar Institucional', meta: 'Programas · Convocatorias' }
    ],
    docs: [
      { nombre: 'Plan de Desarrollo Institucional 2025-2030', tipo: 'Plan', version: 'V2.0', fecha: '12 jun 2026' },
      { nombre: 'Proyecto Educativo Institucional', tipo: 'Documento marco', version: 'V4.1', fecha: '30 abr 2026' },
      { nombre: 'Mapa Estratégico Institucional', tipo: 'Presentación', version: 'V1.3', fecha: '18 may 2026' },
      { nombre: 'Política de Gobierno Corporativo', tipo: 'Política', version: 'V1.0', fecha: '02 mar 2026' }
    ],
    responsables: [
      { ini: 'LG', nombre: 'Laura Gómez', cargo: 'Directora de Planeación Estratégica' },
      { ini: 'AC', nombre: 'Andrés Castaño', cargo: 'Líder Sistema de Gestión Integral' },
      { ini: 'PM', nombre: 'Paula Medina', cargo: 'Analista de Planeación' }
    ],
    software: ['Power BI · Tablero PDI', 'Suite ISO · Documentos', 'Google Workspace']
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
