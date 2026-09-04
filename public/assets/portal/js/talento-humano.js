  /* ═══ Datos de ejemplo ═══ */
  const ORGANIGRAMA = [
    { nivel: 'Gobierno', cajas: [
      { label: 'Consejo de Fundadores', meta: 'Máxima instancia' },
      { label: 'Consejo Directivo', meta: 'Dirección estratégica' },
      { label: 'Revisoría Fiscal', meta: 'Externa' },
      { label: 'Control Interno', meta: 'Evaluación independiente' }
    ]},
    { nivel: 'Dirección general', cajas: [
      { label: 'Rectoría', meta: 'Martha Ruiz Delgado', destacado: true },
      { label: 'Secretaría General', meta: 'Actos administrativos' },
      { label: 'Jurídica', meta: 'Asesoría legal' }
    ]},
    { nivel: 'Direcciones', cajas: [
      { label: 'Vicerrectoría Académica', meta: 'Camilo Naranjo' },
      { label: 'Dirección de Planeación Estratégica y Gestión Humana', meta: 'Laura Gómez' },
      { label: 'Dirección Administrativa, Contable y Financiera', meta: 'Jorge Bermúdez' },
      { label: 'Dirección de Investigación, Proyectos e Innovación', meta: 'Ricardo Osorio' }
    ]}
  ];
  const COMITES = ['Consejo Académico', 'Comité de Planeación', 'Comité Financiero y Administrativo', 'Comité de Investigación', 'Comité de Autoevaluación y Acreditación', 'Comité de Bienestar Institucional'];

  const CARGOS = [
    { cargo: 'Rector', direccion: 'Rectoría', nivel: 'Directivo', codigo: '001' },
    { cargo: 'Vicerrector Académico', direccion: 'Vicerrectoría Académica', nivel: 'Directivo', codigo: '004' },
    { cargo: 'Director de Planeación Estratégica', direccion: 'Planeación y Gestión Humana', nivel: 'Directivo', codigo: '007' },
    { cargo: 'Decano', direccion: 'Decanaturas', nivel: 'Directivo', codigo: '012' },
    { cargo: 'Contador General', direccion: 'Administrativa y Financiera', nivel: 'Profesional', codigo: '021' },
    { cargo: 'Líder del Sistema de Gestión Integral', direccion: 'Planeación y Gestión Humana', nivel: 'Profesional', codigo: '028' },
    { cargo: 'Docente de tiempo completo', direccion: 'Decanaturas', nivel: 'Docente', codigo: '052' }
  ];
  const COMPETENCIAS = [
    { label: 'Orientación al servicio', pct: 88 },
    { label: 'Trabajo en equipo', pct: 84 },
    { label: 'Pensamiento analítico', pct: 76 },
    { label: 'Competencia digital', pct: 71 },
    { label: 'Liderazgo institucional', pct: 69 }
  ];

  const KPIS_TALENTO = [
    { label: 'Colaboradores', valor: '594' },
    { label: 'Evaluaciones cerradas', valor: '81%' },
    { label: 'Horas de capacitación', valor: '4.180' },
    { label: 'Rotación anual', valor: '6,2%' }
  ];
  const DESEMPENO = [
    { area: 'Rectoría', metas: 6, cumplidas: 5, pct: 83, estado: 'Sobresaliente' },
    { area: 'Vicerrectoría Académica', metas: 9, cumplidas: 6, pct: 67, estado: 'Satisfactorio' },
    { area: 'Planeación y Gestión Humana', metas: 8, cumplidas: 6, pct: 75, estado: 'Satisfactorio' },
    { area: 'Investigación e Innovación', metas: 6, cumplidas: 3, pct: 50, estado: 'En riesgo' },
    { area: 'Sistema de Gestión Integral', metas: 7, cumplidas: 4, pct: 57, estado: 'En riesgo' }
  ];

  const BIENESTAR = [
    { icon: '<i class="bi bi-mortarboard"></i>', titulo: 'Formación posgradual', desc: 'Apoyo económico para maestrías y especializaciones de colaboradores con más de dos años.', cta: 'Convocatoria abierta' },
    { icon: '<i class="bi bi-journal-bookmark"></i>', titulo: 'Plan de capacitación', desc: '4.180 horas programadas en competencias digitales, servicio y liderazgo.', cta: 'Ver programación' },
    { icon: '<i class="bi bi-heart"></i>', titulo: 'Salud y seguridad', desc: 'Programa de vigilancia epidemiológica, pausas activas y exámenes periódicos.', cta: 'Agendar' },
    { icon: '<i class="bi bi-trophy"></i>', titulo: 'Reconocimientos', desc: 'Talento que Suma: exaltación trimestral por área y por resultados de proceso.', cta: 'Postular' },
    { icon: '<i class="bi bi-people"></i>', titulo: 'Cultura y deporte', desc: 'Torneos internos, coro institucional y semana cultural del norte del Tolima.', cta: 'Inscribirse' },
    { icon: '<i class="bi bi-bullseye"></i>', titulo: 'Clima organizacional', desc: 'Medición anual con 82% de participación y plan de intervención por área.', cta: 'Ver resultados' }
  ];

  const tagEstado = (estado) => estado === 'Sobresaliente' ? 'tag-ok' : estado === 'En riesgo' ? 'tag-warn' : 'tag-neutral';

  /* ═══ Pestañas ═══ */
  let tabActivo = 'organigrama';
  const TABS = [['organigrama','Organigrama'], ['funciones','Manual de funciones'], ['desempeno','Desempeño'], ['bienestar','Bienestar']];

  function renderTabs(){
    document.getElementById('thTabs').innerHTML = TABS.map(([id,label]) => `
      <button class="th-tab ${tabActivo===id?'active':''}" data-tab="${id}">${label}</button>`).join('');
    document.querySelectorAll('.th-tab').forEach(btn => {
      btn.addEventListener('click', () => { tabActivo = btn.dataset.tab; renderTabs(); renderContenido(); });
    });
  }

  function renderContenido(){
    const cont = document.getElementById('thContenido');

    if (tabActivo === 'organigrama') {
      cont.innerHTML = `
        ${ORGANIGRAMA.map(n => `
          <div class="org-nivel">
            <div class="org-nivel-label">${n.nivel}</div>
            <div class="org-cajas">
              ${n.cajas.map(c => `
                <div class="org-caja ${c.destacado?'destacado':''}">
                  <div class="label">${c.label}</div>
                  <div class="meta">${c.meta}</div>
                </div>`).join('')}
            </div>
          </div>`).join('')}
        <div style="border-top:2px solid var(--color-divider);padding-top:24px;margin-top:12px">
          <h4 style="margin:0 0 14px">Consejos y comités</h4>
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            ${COMITES.map(c => `<span class="tag-outline">${c}</span>`).join('')}
          </div>
        </div>`;

    } else if (tabActivo === 'funciones') {
      cont.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 340px;gap:36px;align-items:start">
          <table class="table">
            <thead><tr><th>Cargo</th><th>Dirección</th><th>Nivel</th><th>Manual</th></tr></thead>
            <tbody>
              ${CARGOS.map(c => `
                <tr style="cursor:pointer">
                  <td><strong>${c.cargo}</strong></td>
                  <td style="opacity:.7">${c.direccion}</td>
                  <td><span class="tag tag-neutral">${c.nivel}</span></td>
                  <td style="color:var(--color-accent-700);font-weight:800">MF-${c.codigo}</td>
                </tr>`).join('')}
            </tbody>
          </table>
          <div style="border:1px solid var(--color-divider);padding:24px">
            <div style="font-size:10px;letter-spacing:.16em;text-transform:uppercase;font-weight:800;opacity:.5;padding-bottom:12px">Competencias institucionales</div>
            ${COMPETENCIAS.map(c => `
              <div class="comp-row">
                <div class="comp-head"><span>${c.label}</span><span style="font-weight:800">${c.pct}%</span></div>
                <div class="comp-bar"><div class="comp-bar-fill" style="width:${c.pct}%"></div></div>
              </div>`).join('')}
          </div>
        </div>`;

    } else if (tabActivo === 'desempeno') {
      cont.innerHTML = `
        <div class="kpis">
          ${KPIS_TALENTO.map(k => `<div class="kpi"><div class="kpi-label">${k.label}</div><div class="kpi-value">${k.valor}</div></div>`).join('')}
        </div>
        <h4 style="margin:0 0 4px">Cascadeo de metas 2026</h4>
        <table class="table">
          <thead><tr><th>Área</th><th>Metas</th><th>Cumplidas</th><th>Avance</th><th>Evaluación</th></tr></thead>
          <tbody>
            ${DESEMPENO.map(d => `
              <tr>
                <td><strong>${d.area}</strong></td>
                <td style="opacity:.7">${d.metas}</td>
                <td style="opacity:.7">${d.cumplidas}</td>
                <td style="width:200px"><span style="display:block;height:6px;background:var(--color-surface)"><span style="display:block;height:6px;background:var(--color-accent);width:${d.pct}%"></span></span></td>
                <td><span class="tag ${tagEstado(d.estado)}">${d.estado}</span></td>
              </tr>`).join('')}
          </tbody>
        </table>`;

    } else {
      cont.innerHTML = `
        <div class="bienestar-grid">
          ${BIENESTAR.map(b => `
            <div class="bienestar-card">
              <span class="ic">${b.icon}</span>
              <span class="titulo">${b.titulo}</span>
              <span class="desc">${b.desc}</span>
              <span class="cta">${b.cta}</span>
            </div>`).join('')}
        </div>`;
    }
  }

  renderTabs();
  renderContenido();
