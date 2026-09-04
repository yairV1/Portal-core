  // Saludo según la hora del día
  (function () {
    const el = document.getElementById('saludoKicker');
    if (!el) return;
    const hora = new Date().getHours();
    el.textContent = (hora >= 5 && hora < 12) ? 'Buenos días'
      : (hora >= 12 && hora < 19) ? 'Buenas tardes'
      : 'Buenas noches';
  })();

  // ——— Datos de ejemplo: reemplaza cada arreglo con tus propios datos ———
  const KPIS = [
    { label: 'Estudiantes activos', valor: '8.742', delta: '+4,1% vs 2026-I' },
    { label: 'Retención', valor: '89,4%', delta: 'Meta 91,0%' },
    { label: 'Avance PDI', valor: '62%', delta: '48 metas cascadeadas' },
    { label: 'Ejecución presupuestal', valor: '76,8%', delta: 'Al 31 de julio' },
    { label: 'Colaboradores', valor: '594', delta: '412 docentes' }
  ];

  const ACCESOS = [
    { label: 'Manual de funciones', meta: 'Talento Humano', icon: '<i class="bi bi-journal-bookmark"></i>' },
    { label: 'Repositorio documental', meta: 'Gestión Documental', icon: '<i class="bi bi-folder2"></i>' },
    { label: 'Mapa de procesos', meta: 'SGI', icon: '<i class="bi bi-folder2-open"></i>' },
    { label: 'Tablero Rectoría', meta: 'Power BI', icon: '<i class="bi bi-bar-chart"></i>' },
    { label: 'Normatividad', meta: 'Acuerdos y resoluciones', icon: '<i class="bi bi-file-earmark-text"></i>' },
    { label: 'Organigrama', meta: 'Estructura 2026', icon: '<i class="bi bi-diagram-3"></i>' },
    { label: 'Mesa de ayuda', meta: 'Soporte TI', icon: '<i class="bi bi-tools"></i>' },
    { label: 'Correo institucional', meta: 'Google Workspace', icon: '<i class="bi bi-envelope"></i>' }
  ];

  const DOCS_RECIENTES = [
    { nombre: 'Manual de Funciones y Competencias', area: 'Gestión Humana', version: 'V4.0', fecha: '28 jul' },
    { nombre: 'Manual del Sistema de Gestión Integral', area: 'SGI', version: 'V2.0', fecha: '26 jul' },
    { nombre: 'Mapa de Procesos Institucional', area: 'SGI', version: 'V3.0', fecha: '21 jul' },
    { nombre: 'Reglamento Estudiantil', area: 'Secretaría General', version: 'V5.0', fecha: '20 jul' },
    { nombre: 'Informe de Ejecución Presupuestal', area: 'Financiera', version: 'V1.0', fecha: '30 jul' }
  ];

  const NOTICIAS = [
    { categoria: 'Acreditación', titulo: 'Siete programas alcanzan acreditación de alta calidad', fecha: '24 jul 2026' },
    { categoria: 'Transformación digital', titulo: 'Portal CORE entra en su fase piloto con 120 usuarios', fecha: '22 jul 2026' },
    { categoria: 'Bienestar', titulo: 'Abierta la convocatoria de formación posgradual 2026', fecha: '19 jul 2026' }
  ];

  const PENDIENTES = [
    { titulo: 'Aprobar Manual del SGI V2.0', meta: 'Control documental · vence hoy', color: '#f15a29' },
    { titulo: 'Firmar Resolución 209 de 2026', meta: 'Secretaría General · 2 días', color: '#9e1f63' },
    { titulo: 'Revisar plan de acción de Bienestar', meta: 'Planeación · 4 días', color: '#9e1f63' },
    { titulo: 'Cerrar evaluación de desempeño del equipo', meta: 'Gestión Humana · 12 días', color: '#999' }
  ];

  const EVENTOS = [
    { dia: '04', mes: 'Ago', titulo: 'Consejo Directivo ordinario', hora: '08:00 · Sala de juntas' },
    { dia: '06', mes: 'Ago', titulo: 'Comité de Autoevaluación y Acreditación', hora: '10:00 · Virtual' },
    { dia: '11', mes: 'Ago', titulo: 'Rendición de cuentas 2026-I', hora: '15:00 · Auditorio' },
    { dia: '14', mes: 'Ago', titulo: 'Cierre de cascadeo de metas', hora: 'Todo el día' }
  ];

  const CUMPLEANOS = [
    { ini: 'DV', nombre: 'Diego Valencia', fecha: 'Hoy' },
    { ini: 'YT', nombre: 'Yolanda Torres', fecha: '3 ago' },
    { ini: 'NP', nombre: 'Natalia Peña', fecha: '7 ago' }
  ];

  // ——— Render ———
  document.getElementById('kpis').innerHTML = KPIS.map(k => `
    <div class="kpi">
      <div class="kpi-label">${k.label}</div>
      <div class="kpi-value">${k.valor}</div>
      <div class="kpi-delta">${k.delta}</div>
    </div>`).join('');

  document.getElementById('accesos').innerHTML = ACCESOS.map(a => `
    <div class="acceso">
      <span class="ic">${a.icon}</span>
      <span class="label">${a.label}</span>
      <span class="meta">${a.meta}</span>
    </div>`).join('');

  document.getElementById('docsRecientes').innerHTML = DOCS_RECIENTES.map(d => `
    <tr>
      <td><strong>${d.nombre}</strong></td>
      <td style="opacity:.7">${d.area}</td>
      <td><span class="tag">${d.version}</span></td>
      <td style="opacity:.7">${d.fecha}</td>
    </tr>`).join('');

  document.getElementById('noticias').innerHTML = NOTICIAS.map(n => `
    <div class="card">
      <div class="noticia-foto">Fotografía institucional</div>
      <div class="noticia-body">
        <div class="noticia-cat">${n.categoria}</div>
        <div class="noticia-titulo">${n.titulo}</div>
        <div class="noticia-fecha">${n.fecha}</div>
      </div>
    </div>`).join('');

  document.getElementById('pendientes').innerHTML = PENDIENTES.map(p => `
    <div class="pendiente">
      <span class="dotp" style="background:${p.color}"></span>
      <span style="flex:1">
        <span class="t" style="display:block">${p.titulo}</span>
        <span class="m" style="display:block">${p.meta}</span>
      </span>
    </div>`).join('');

  document.getElementById('eventos').innerHTML = EVENTOS.map(e => `
    <div class="evento">
      <span class="fecha">
        <span class="dia" style="display:block">${e.dia}</span>
        <span class="mes" style="display:block">${e.mes}</span>
      </span>
      <span style="flex:1">
        <span class="t" style="display:block">${e.titulo}</span>
        <span class="h" style="display:block">${e.hora}</span>
      </span>
    </div>`).join('');

  document.getElementById('cumpleanos').innerHTML = CUMPLEANOS.map(c => `
    <div class="cumple">
      <span class="ini">${c.ini}</span>
      <span class="n">${c.nombre}</span>
      <span class="f">${c.fecha}</span>
    </div>`).join('');
