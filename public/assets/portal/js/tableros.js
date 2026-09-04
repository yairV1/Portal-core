  /* ═══ Datos de ejemplo — los mismos que van en las tablas de MySQL ═══ */
  const KPIS_TABLERO = [
    { label: 'Estudiantes activos', valor: '8.742', meta: '9.000', pct: 97 },
    { label: 'Retención', valor: '89,4%', meta: '91,0%', pct: 98 },
    { label: 'Graduados 2026', valor: '1.126', meta: '1.200', pct: 94 },
    { label: 'Satisfacción', valor: '4,3/5', meta: '4,5', pct: 96 },
    { label: 'Avance PDI', valor: '62%', meta: '70%', pct: 89 }
  ];
  const MATRICULA_FACULTAD = [
    { label: 'Tecnologías y Transf. Digital', valor: '2.410', h: '68%' },
    { label: 'Ciencias Administrativas', valor: '3.180', h: '90%' },
    { label: 'Infraestructura y Sostenibilidad', valor: '1.520', h: '43%' },
    { label: 'Centro de Idiomas', valor: '980', h: '28%' },
    { label: 'Educación continua', valor: '652', h: '19%' }
  ];
  const EJECUCION_PRESUPUESTAL = [
    { label: 'Funcionamiento', pct: '81%' },
    { label: 'Inversión', pct: '64%' },
    { label: 'Investigación', pct: '58%' },
    { label: 'Bienestar', pct: '73%' }
  ];
  const ALERTAS_INDICADOR = [
    { texto: 'Retención estudiantil 1,6 pp por debajo de la meta institucional.' },
    { texto: 'Ejecución de investigación en 58%; requiere plan de choque a septiembre.' },
    { texto: '6 hallazgos de auditoría interna sin plan de mejoramiento cargado.' }
  ];

  /* ═══ CONTENIDO: TABLEROS ═══ */
  const TABS = ['Rectoría', 'Directivos', 'Académico', 'Financiero'];
  document.getElementById('tableroTabs').innerHTML = TABS.map((t,i) => `
    <button data-tab="${t}" style="padding:10px 18px;border:0;border-right:1px solid var(--color-divider);cursor:pointer;font-weight:800;font-size:12.5px;background:${i===0?'var(--color-accent)':'transparent'};color:${i===0?'#fff':'var(--color-text)'}">${t}</button>`).join('');
  document.getElementById('tableroTabs').addEventListener('click', e => {
    if (e.target.tagName !== 'BUTTON') return;
    document.querySelectorAll('#tableroTabs button').forEach(b => { b.style.background = 'transparent'; b.style.color = 'var(--color-text)'; });
    e.target.style.background = 'var(--color-accent)'; e.target.style.color = '#fff';
  });

  document.getElementById('kpisTablero').innerHTML = KPIS_TABLERO.map(k => `
    <div class="kpi">
      <div class="kpi-label">${k.label}</div>
      <div class="kpi-value">${k.valor}</div>
      <div style="font-size:12px;opacity:.6">Meta ${k.meta}</div>
      <div style="height:5px;background:var(--color-surface);margin-top:10px"><div style="height:5px;background:var(--color-accent);width:${k.pct}%"></div></div>
    </div>`).join('');

  document.getElementById('barras').innerHTML = MATRICULA_FACULTAD.map(b => `
    <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;gap:8px;height:100%">
      <span style="font-weight:800;font-size:13px">${b.valor}</span>
      <span style="background:var(--color-accent);height:${b.h}"></span>
      <span style="font-size:10.5px;opacity:.6;line-height:1.2;min-height:26px">${b.label}</span>
    </div>`).join('');

  document.getElementById('ejecucion').innerHTML = EJECUCION_PRESUPUESTAL.map(e => `
    <div style="padding:9px 0">
      <div style="display:flex;justify-content:space-between;font-size:12.5px;padding-bottom:6px"><span>${e.label}</span><span style="font-weight:800">${e.pct}</span></div>
      <div style="height:6px;background:var(--color-surface)"><div style="height:6px;background:var(--color-accent-2);width:${e.pct}"></div></div>
    </div>`).join('');

  document.getElementById('alertasKpi').innerHTML = ALERTAS_INDICADOR.map(a => `
    <div style="display:flex;gap:12px;padding:11px 0;border-top:1px solid var(--color-divider)">
      <span style="color:var(--color-accent-2);flex:0 0 auto"><i class="bi bi-exclamation-triangle"></i></span>
      <span style="font-size:12.5px;flex:1">${a.texto}</span>
    </div>`).join('');
