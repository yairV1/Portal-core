  /* ═══ Datos de ejemplo — un módulo por dirección, con sus sub-secciones ═══ */
  const SITEMAP = [
    { nivel: 'Módulo 01', label: 'Inicio', icon: '<i class="bi bi-house-door"></i>', hijos: ['Bienvenida y perfil', 'Indicadores rápidos', 'Accesos y favoritos', 'Pendientes y agenda', 'Novedades'] },
    { nivel: 'Módulo 02', label: 'Tableros Estratégicos', icon: '<i class="bi bi-bar-chart"></i>', hijos: ['Dashboard Rectoría', 'Dashboard Directivos', 'Tableros por área', 'Power BI embebido', 'Alertas de indicador'] },
    { nivel: 'Módulo 03', label: 'Gestión Institucional', icon: '<i class="bi bi-bank"></i>', hijos: ['PDI 2025-2030', 'PEI', 'Políticas', 'Mapa estratégico', 'Planes de acción'] },
    { nivel: 'Módulo 04', label: 'Sistema de Gestión Integral', icon: '<i class="bi bi-folder2-open"></i>', hijos: ['Mapa de procesos', 'Caracterizaciones', 'Procedimientos e instructivos', 'Auditorías', 'Riesgos y mejoramiento'] },
    { nivel: 'Módulo 05', label: 'Gestión Documental', icon: '<i class="bi bi-folder2"></i>', hijos: ['Repositorio institucional', 'Control de versiones', 'Documentos vigentes', 'Archivo histórico', 'Buscador documental'] },
    { nivel: 'Módulo 06', label: 'Talento Humano', icon: '<i class="bi bi-people"></i>', hijos: ['Organigrama', 'Manual de funciones', 'Desempeño y cascadeo', 'Competencias y capacitación', 'Bienestar y reconocimientos'] },
    { nivel: 'Módulo 07', label: 'Administrativa y Financiera', icon: '<i class="bi bi-cash-coin"></i>', hijos: ['Contabilidad', 'Tesorería y cartera', 'Presupuesto', 'Estados financieros', 'Servicios generales'] },
    { nivel: 'Módulo 08', label: 'Vicerrectoría Académica', icon: '<i class="bi bi-mortarboard"></i>', hijos: ['Decanaturas', 'Programas', 'Registro y Control', 'Consejos académicos', 'Autoevaluación'] },
    { nivel: 'Módulo 09', label: 'Investigación e Innovación', icon: '<i class="bi bi-stars"></i>', hijos: ['Grupos y semilleros', 'Proyectos', 'Proyección social', 'Egresados', 'Relaciones interinstitucionales'] },
    { nivel: 'Módulo 10', label: 'Novedades', icon: '<i class="bi bi-newspaper"></i>', hijos: ['Noticias', 'Comunicados', 'Circulares', 'Eventos', 'Cumpleaños y reconocimientos'] },
    { nivel: 'Módulo 11', label: 'Normatividad', icon: '<i class="bi bi-file-earmark-text"></i>', hijos: ['Acuerdos', 'Resoluciones', 'Reglamentos', 'Manual institucional', 'Normativa externa'] },
    { nivel: 'Módulo 12', label: 'Aplicaciones', icon: '<i class="bi bi-grid"></i>', hijos: ['Correo y Workspace', 'ERP y contable', 'Campus y académico', 'Power BI', 'Mesa de ayuda'] }
  ];

  const CONTENIDO_TIPO = ['Documentación', 'Manual de funciones', 'Políticas', 'Procesos', 'Procedimientos', 'Formatos', 'Instructivos', 'Guías', 'Protocolos', 'Videos', 'Presentaciones', 'Reportes', 'Indicadores', 'Normatividad', 'Archivo histórico', 'Noticias', 'Proyectos', 'Cronogramas'];

  document.getElementById('sitemapGrid').innerHTML = SITEMAP.map(m => `
    <div class="sitemap-card">
      <div class="sitemap-kicker">
        <span>${m.icon}</span>
        <span class="sitemap-nivel">${m.nivel}</span>
      </div>
      <div class="sitemap-titulo">${m.label}</div>
      <div class="sitemap-rule"></div>
      ${m.hijos.map(h => `<div class="sitemap-hijo">${h}</div>`).join('')}
    </div>`).join('');

  document.getElementById('contenidoTipo').innerHTML = CONTENIDO_TIPO.map(c => `<span class="tag-outline">${c}</span>`).join('');
