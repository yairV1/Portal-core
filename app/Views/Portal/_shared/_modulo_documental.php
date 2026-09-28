<?php
/**
 * Centro documental de una dirección — lo comparten Gestión Institucional,
 * SGI, Vicerrectoría Académica, Investigación e Innovación y Administrativa
 * y Financiera (antes eran 5 vistas copiadas, ~270 líneas cada una).
 *
 * La vista define $MD:
 *   'ruta'        => '/gestion-institucional'
 *   'titulo'      => título por defecto (si Contenido Landing/BD no trae uno)
 *   'desc'        => descripción por defecto
 *   'buscar'      => placeholder del buscador
 *   'vacioCategorias' => texto cuando no hay categorías
 *   'areas'       => opcional: ['clave' => ['Etiqueta', 'icono'], …] — pestañas
 *                    por área que viajan en ?area= (hoy solo Financiera).
 * Variables de PortalController.php: $direccion, $moduloKicker, $moduloTitulo,
 * $moduloDesc, $moduloKpis, $moduloAreas, $moduloResponsables,
 * $moduloSoftware, $moduloDocumentos, $moduloFormatos, $carpetaActual,
 * $subcarpetas, $archivosRecientes, $terminoBusqueda, $resultadosBusqueda,
 * $areaActiva, $csrf (y las del explorador documental).
 */
$esAdminDoc = usuario_admin_de($direccion['id'] ?? null);
$ruta = $MD['ruta'];
$areas = $MD['areas'] ?? [];
$qsArea = $areas ? '?area=' . urlencode($areaActiva) : '';
$urlPanel = BASE_URL . $ruta . $qsArea;
$nombreArea = $areas ? ($areas[$areaActiva][0] ?? '') : '';

// Enlace de "ver/descargar" y de "abrir en el editor" de un archivo del
// listado (puede venir de una carpeta o de un documento de la dirección).
$urlVerArchivo = fn (array $a) => $a['origen'] === 'carpeta'
    ? BASE_URL . $ruta . '/carpetas/descargar?id=' . (int) $a['id']
    : BASE_URL . '/documentos/descargar?tipo=direccion&id=' . (int) $a['id'];
$filaArchivo = function (array $a, bool $conOrigen) use ($urlVerArchivo, $esAdminDoc, $ruta, $urlPanel, $csrf, $direccion) {
    $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf';
    ?>
    <div class="doc-archivo-row">
      <span class="ic" aria-hidden="true"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
      <span class="info">
        <span class="nombre"><?= e($a['nombre']) ?></span>
        <span class="meta">
          <?php if ($conOrigen): ?><span class="doc-tag-origen"><?= ['carpeta' => 'Carpeta', 'documento' => 'Documento', 'formato' => 'Formato'][$a['origen']] ?? '' ?></span><?php endif; ?>
          <?php if (!$conOrigen && !empty($a['carpeta_nombre'])): ?><span class="doc-tag-origen"><?= e($a['carpeta_nombre']) ?></span><?php endif; ?>
          <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
          <?= e((new DateTime($a['fecha_raw']))->format('d M Y')) ?>
        </span>
      </span>
      <span class="doc-actions">
        <a class="btn btn-sm" href="<?= e($urlVerArchivo($a)) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>" aria-hidden="true"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
        <?php if (onlyoffice_configurado() && onlyoffice_editable($a['archivo'])): ?>
          <a class="btn btn-sm btn-icon" href="<?= BASE_URL ?>/editor?tipo=<?= $a['origen'] === 'carpeta' ? 'carpeta' : 'direccion' ?>&id=<?= (int) $a['id'] ?>&volver=<?= urlencode($urlPanel) ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal" aria-label="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> <?= e($a['nombre']) ?> dentro del portal"><i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>" aria-hidden="true"></i></a>
        <?php endif; ?>
        <?php if ($conOrigen && $esAdminDoc && $a['origen'] === 'carpeta'): ?>
          <form action="<?= BASE_URL . $ruta ?>/carpetas/eliminar" method="post" data-confirm="¿Eliminar este documento?" data-confirm-text="<?= e($a['nombre']) ?>" data-confirm-ok="Eliminar">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <input type="hidden" name="archivo_id" value="<?= (int) $a['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger-soft btn-icon" aria-label="Eliminar <?= e($a['nombre']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
          </form>
        <?php endif; ?>
      </span>
    </div>
    <?php
};
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<?php ui_page_header([
    'title'   => $moduloTitulo ?: $MD['titulo'],
    'desc'    => $moduloDesc ?: $MD['desc'],
    'eyebrow' => $moduloKicker ?: null,
    'back'    => $carpetaActual ? ['href' => $urlPanel, 'label' => $nombreArea ? 'Volver al panel de ' . $nombreArea : 'Volver a Documentos'] : null,
]); ?>

<?php if ($areas && !$carpetaActual): ?>
  <nav class="segmented doc-areas" aria-label="Área del centro documental">
    <?php foreach ($areas as $clave => [$etq, $icono]): ?>
      <a href="<?= BASE_URL . $ruta ?>?area=<?= e($clave) ?>" class="segmented-item<?= $areaActiva === $clave ? ' active' : '' ?>"<?= $areaActiva === $clave ? ' aria-current="page"' : '' ?>>
        <i class="bi bi-<?= e($icono) ?>" aria-hidden="true"></i> <?= e($etq) ?>
      </a>
    <?php endforeach; ?>
  </nav>
<?php endif; ?>

<?php if ($carpetaActual): ?>
  <?php $rutaModuloActual = $ruta; require ROOT_PATH . '/app/Views/Portal/_shared/_explorador_documental.php'; ?>

<?php else: ?>

  <div class="filter-bar">
    <form action="<?= BASE_URL . $ruta ?>" method="get" class="input-group search-field" role="search" data-no-loading>
      <?php if ($areas): ?><input type="hidden" name="area" value="<?= e($areaActiva) ?>"><?php endif; ?>
      <i class="bi bi-search input-icon" aria-hidden="true"></i>
      <input class="input" type="search" name="buscar" value="<?= e($terminoBusqueda) ?>" placeholder="<?= e($MD['buscar']) ?>" aria-label="Buscar documentos">
    </form>
    <div class="filter-bar-end">
      <?php $rutaModuloActual = $ruta; require ROOT_PATH . '/app/Views/Portal/_shared/_agregar_documento.php'; ?>
    </div>
  </div>

  <?php if ($terminoBusqueda !== ''): ?>

    <div class="section-header">
      <h2 class="section-heading">Resultados para "<?= e($terminoBusqueda) ?>"</h2>
      <a href="<?= e($urlPanel) ?>" class="btn btn-link btn-sm"><i class="bi bi-x-lg" aria-hidden="true"></i> Limpiar búsqueda</a>
    </div>
    <?php if (!$resultadosBusqueda): ?>
      <?php ui_empty_state([
          'icon' => 'search', 'title' => 'Sin resultados',
          'text' => 'No encontramos ningún documento que coincida con "' . $terminoBusqueda . '"' . ($nombreArea ? ' en ' . $nombreArea : '') . '.',
      ]); ?>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($resultadosBusqueda as $a) { $filaArchivo($a, false); } ?>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <div class="section-header"><h2 class="section-heading">Categorías</h2></div>
    <?php if (!$subcarpetas): ?>
      <div class="empty-state">
        <div class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></div>
        <h4>Aún no hay categorías</h4>
        <p><?= e($MD['vacioCategorias']) ?></p>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL . $ruta ?>/carpetas/crear" method="post" class="doc-empty-create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <?php if ($areas): ?><input type="hidden" name="area" value="<?= e($areaActiva) ?>"><?php endif; ?>
            <input type="text" name="nombre" placeholder="Nombre de la categoría" aria-label="Nombre de la categoría" required class="input">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear categoría</button>
          </form>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="doc-categorias">
        <?php foreach ($subcarpetas as $c): ?>
          <article class="doc-categoria-card">
            <a href="<?= BASE_URL . $ruta ?>?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
              <span class="ic" aria-hidden="true"><i class="bi bi-folder-fill" aria-hidden="true"></i></span>
              <span class="nombre"><?= e($c['nombre']) ?></span>
              <span class="meta"><?= (int) $c['total_archivos'] ?> documento<?= ((int) $c['total_archivos'] === 1) ? '' : 's' ?></span>
            </a>
            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL . $ruta ?>/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta categoría?" data-confirm-text="También se eliminará todo lo que tenga dentro." data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger-soft btn-icon doc-cat-borrar" aria-label="Eliminar categoría <?= e($c['nombre']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL . $ruta ?>/carpetas/crear" method="post" class="doc-categoria-card doc-categoria-card--create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <?php if ($areas): ?><input type="hidden" name="area" value="<?= e($areaActiva) ?>"><?php endif; ?>
            <span class="ic" aria-hidden="true"><i class="bi bi-folder-plus" aria-hidden="true"></i></span>
            <input type="text" name="nombre" placeholder="Nueva categoría" aria-label="Nombre de la nueva categoría" required class="input input-sm">
            <button type="submit" class="btn btn-sm">Crear</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="section-header section-header--spaced" id="doc-agregar"><h2 class="section-heading">Archivos recientes</h2></div>
    <?php if (!$archivosRecientes): ?>
      <?php ui_empty_state([
          'icon' => 'file-earmark-text', 'title' => 'Aún no hay documentos',
          'text' => $nombreArea ? 'Sube el primer documento de ' . $nombreArea . ' dentro de una categoría para verlo acá.' : 'Sube el primer archivo dentro de una categoría para verlo acá.',
          'compact' => true,
      ]); ?>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($archivosRecientes as $a) { $filaArchivo($a, true); } ?>
      </div>
    <?php endif; ?>

    <?php
    // Documentos/Formatos creados pero todavía sin archivo adjunto — siguen
    // necesitando el paso de "Subir" de siempre (ver DocumentoController.php).
    $pendientes = array_filter(array_merge(
        array_map(fn ($d) => $d + ['tipoEtq' => 'Documento'], $moduloDocumentos),
        array_map(fn ($f) => $f + ['tipoEtq' => 'Formato'], $moduloFormatos)
    ), fn ($d) => !$d['archivo']);
    ?>
    <?php if ($esAdminDoc && $pendientes): ?>
      <div class="section-header section-header--spaced">
        <h2 class="section-heading">Pendientes de adjuntar</h2>
        <span class="section-desc">Ya tienen nombre, les falta el archivo</span>
      </div>
      <div class="doc-archivos">
        <?php foreach ($pendientes as $p): ?>
          <div class="doc-archivo-row">
            <span class="ic" aria-hidden="true"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i></span>
            <span class="info">
              <span class="nombre"><?= e($p['nombre']) ?></span>
              <span class="meta"><span class="doc-tag-origen"><?= e($p['tipoEtq']) ?></span></span>
            </span>
            <span class="doc-actions">
              <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="tipo" value="direccion">
                <input type="hidden" name="volver" value="<?= e($ruta) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $p['id'] ?>">
                <label class="file-btn btn btn-sm btn-primary">
                  <i class="bi bi-upload" aria-hidden="true"></i> Subir
                  <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required onchange="this.form.submit()" aria-label="Subir archivo para <?= e($p['nombre']) ?>">
                </label>
              </form>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>

  <div class="section-header section-header--spaced"><h2 class="section-heading">Indicadores</h2></div>
  <div class="kpis">
    <?php if (!$moduloKpis): ?>
      <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></div><p>Sin indicadores por ahora.</p></div>
    <?php else: foreach ($moduloKpis as $k): ?>
      <div class="kpi">
        <div class="kpi-head"><div class="kpi-label"><?= e($k['label']) ?></div></div>
        <div class="kpi-value"><?= e($k['valor']) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="modulo-grid">
    <section>
      <div class="section-header section-header--spaced"><h2 class="section-heading">Áreas del módulo</h2></div>
      <?php if (!$moduloAreas): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></div><p>Sin áreas registradas por ahora.</p></div>
      <?php else: ?>
        <div class="area-lista">
          <?php foreach ($moduloAreas as $a): ?>
            <div class="area-item" id="<?= e(sb_slug($a['label'])) ?>">
              <span class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></span>
              <span class="doc-flex-grow">
                <span class="label"><?= e($a['label']) ?></span>
                <span class="meta"><?= e($a['meta']) ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <aside class="doc-side-stack">
      <?php require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_lateral.php'; ?>
    </aside>
  </div>

<?php endif; ?>
