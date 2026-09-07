  /* El grid de módulos ya lo renderiza Mapa.php con datos reales desde
     $pdo (ver PortalController.php). CONTENIDO_TIPO no tiene tabla
     propia todavía (es vocabulario fijo de tipos de contenido, no un
     listado que cambie por dirección). */
  const CONTENIDO_TIPO = ['Documentación', 'Manual de funciones', 'Políticas', 'Procesos', 'Procedimientos', 'Formatos', 'Instructivos', 'Guías', 'Protocolos', 'Videos', 'Presentaciones', 'Reportes', 'Indicadores', 'Normatividad', 'Archivo histórico', 'Noticias', 'Proyectos', 'Cronogramas'];

  document.getElementById('contenidoTipo').innerHTML = CONTENIDO_TIPO.map(c => `<span class="tag-outline">${c}</span>`).join('');
