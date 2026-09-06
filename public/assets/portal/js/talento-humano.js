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
    { label: 'Colaboradores', valor: '594', estado: 'accent' },
    { label: 'Evaluaciones cerradas', valor: '81%', estado: 'success' },
    { label: 'Horas de capacitación', valor: '4.180', estado: 'accent' },
    { label: 'Rotación anual', valor: '6,2%', estado: 'warning' }
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
  const COLOR_ESTADO = { success: 'var(--color-success)', warning: 'var(--color-warning)', accent: 'var(--color-accent)' };

  /* ═══ Pestañas ═══ */
  let tabActivo = 'organigrama';
  const TABS = [['organigrama','Organigrama'], ['funciones','Manual de funciones'], ['desempeno','Desempeño'], ['bienestar','Bienestar']];

  function renderTabs(){
    document.getElementById('thTabs').innerHTML = `<div class="pill-tabs">` + TABS.map(([id,label]) => `
      <button type="button" class="pill-tab ${tabActivo===id?'active':''}" data-tab="${id}">${label}</button>`).join('') + `</div>`;
    document.querySelectorAll('.pill-tab').forEach(btn => {
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
        <div class="subsection">
          <h4 class="section-title">Consejos y comités</h4>
          <div class="chip-row">
            ${COMITES.map(c => `<span class="tag-outline">${c}</span>`).join('')}
          </div>
        </div>`;

    } else if (tabActivo === 'funciones') {
      cont.innerHTML = `
        <div class="two-col">
          <table class="table">
            <thead><tr><th>Cargo</th><th>Dirección</th><th>Nivel</th><th>Manual</th></tr></thead>
            <tbody>
              ${CARGOS.map(c => `
                <tr>
                  <td><strong>${c.cargo}</strong></td>
                  <td class="text-muted">${c.direccion}</td>
                  <td><span class="tag tag-neutral">${c.nivel}</span></td>
                  <td class="text-accent">MF-${c.codigo}</td>
                </tr>`).join('')}
            </tbody>
          </table>
          <div class="box-card">
            <div class="side-box-title">Competencias institucionales</div>
            ${COMPETENCIAS.map(c => `
              <div class="comp-row">
                <div class="comp-head"><span>${c.label}</span><strong>${c.pct}%</strong></div>
                <div class="comp-bar"><div class="comp-bar-fill" style="width:${c.pct}%"></div></div>
              </div>`).join('')}
          </div>
        </div>`;

    } else if (tabActivo === 'desempeno') {
      cont.innerHTML = `
        <div class="kpis">
          ${KPIS_TALENTO.map((k, i) => `<div class="kpi"><div class="kpi-label">${k.label}</div><div class="kpi-value">${k.valor}</div><div class="kpi-foot"><span></span><span class="kpi-spark" id="thKpiSpark${i}"></span></div></div>`).join('')}
        </div>
        <h4 class="section-title">Cascadeo de metas 2026</h4>
        <table class="table">
          <thead><tr><th>Área</th><th>Metas</th><th>Cumplidas</th><th>Avance</th><th>Evaluación</th></tr></thead>
          <tbody>
            ${DESEMPENO.map(d => `
              <tr>
                <td><strong>${d.area}</strong></td>
                <td class="text-muted">${d.metas}</td>
                <td class="text-muted">${d.cumplidas}</td>
                <td class="progress-cell"><span class="progress-track"><span class="progress-fill" style="width:${d.pct}%"></span></span></td>
                <td><span class="tag ${tagEstado(d.estado)}">${d.estado}</span></td>
              </tr>`).join('')}
          </tbody>
        </table>`;
      KPIS_TALENTO.forEach((k, i) => {
        window.sparkline(document.getElementById('thKpiSpark' + i), window.tendenciaSintetica(k.valor), { color: COLOR_ESTADO[k.estado] });
      });

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
