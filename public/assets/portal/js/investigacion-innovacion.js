  /* ═══ Datos de ejemplo — Investigación e Innovación ═══ */
  const MODULO = {
    kicker: 'Dirección de Investigación, Proyectos e Innovación',
    titulo: 'Investigación e Innovación',
    desc: 'Grupos de investigación, semilleros, proyectos, proyección social, emprendimiento y egresados.',
    kpis: [
      { label: 'Grupos', valor: '6' },
      { label: 'Proyectos activos', valor: '23' },
      { label: 'Productos 2026', valor: '48' },
      { label: 'Egresados vinculados', valor: '1.126' }
    ],
    areas: [
      { label: 'Innovación y Desarrollo', meta: 'Laboratorio de innovación' },
      { label: 'Proyección Social, Emprendimiento y Egresados', meta: 'Prácticas · Red de egresados' },
      { label: 'Relaciones Interinstitucionales', meta: '17 convenios activos' },
      { label: 'Gestión Comercial', meta: 'Portafolio de servicios' }
    ],
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
