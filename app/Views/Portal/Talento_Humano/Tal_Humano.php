<?php
$titulo = 'Talento Humano';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
/** Variables de PortalController.php: $direccion, $organigrama, $competencias,
 * $carpetaActual, $subcarpetas, $archivosRecientes, $terminoBusqueda,
 * $resultadosBusqueda, $csrf (y las del explorador documental). */
$esAdminDoc = usuario_admin_de($direccion['id'] ?? null);
$urlVerTH = fn (array $a) => BASE_URL . '/talento-humano/carpetas/descargar?id=' . (int) $a['id'];
$filaArchivoTH = function (array $a, bool $conCarpeta) use ($urlVerTH, $esAdminDoc, $csrf, $direccion) {
    $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf';
    ?>
    <div class="doc-archivo-row">
      <span class="ic" aria-hidden="true"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
      <span class="info">
        <span class="nombre"><?= e($a['nombre']) ?></span>
        <span class="meta">
          <?php if ($conCarpeta && !empty($a['carpeta_nombre'])): ?><span class="doc-tag-origen"><?= e($a['carpeta_nombre']) ?></span><?php endif; ?>
          <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
          <?= e((new DateTime($a['fecha_raw']))->format('d M Y')) ?>
        </span>
      </span>
      <span class="doc-actions">
        <a class="btn btn-sm" href="<?= e($urlVerTH($a)) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>" aria-hidden="true"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
        <?php if (onlyoffice_configurado() && onlyoffice_editable($a['archivo'])): ?>
          <a class="btn btn-sm btn-icon" href="<?= BASE_URL ?>/editor?tipo=carpeta&id=<?= (int) $a['id'] ?>&volver=<?= urlencode(BASE_URL . '/talento-humano') ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal" aria-label="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> <?= e($a['nombre']) ?> dentro del portal"><i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>" aria-hidden="true"></i></a>
        <?php endif; ?>
        <?php if (!$conCarpeta && $esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/talento-humano/carpetas/eliminar" method="post" data-confirm="¿Eliminar este documento?" data-confirm-text="<?= e($a['nombre']) ?>" data-confirm-ok="Eliminar">
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
// Expedientes del personal: las 3 páginas propias de Talento Humano (mismas
// rutas que el submenú del panel lateral), solo si el rol puede verlas.
$EXPEDIENTES = [
    ['/talento-humano/hojas-de-vida', 'person-vcard', 'Hojas de vida', 'Una por empleado, con su archivo'],
    ['/talento-humano/contratos', 'file-earmark-ruled', 'Contratos', 'Tipo de contrato y vigencia'],
    ['/talento-humano/certificaciones-laborales', 'award', 'Certificaciones laborales', 'Con fecha de expedición'],
];
$EXPEDIENTES = array_filter($EXPEDIENTES, fn ($x) => usuario_puede_ver_ruta($x[0]));
$verContrataciones = $esAdminDoc && usuario_puede_ver_archivo_de('contrataciones');
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/tableros.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/talento-humano.css') ?>">

<?php ui_page_header([
    'title'   => 'Talento Humano',
    'desc'    => 'Hojas de vida, contratos y certificaciones laborales, organizados en un solo lugar.',
    'back'    => $carpetaActual ? ['href' => BASE_URL . '/talento-humano', 'label' => 'Volver a Documentos'] : null,
    'actions' => $verContrataciones && !$carpetaActual ? function () { ?>
        <a href="<?= BASE_URL ?>/contrataciones" class="btn"><i class="bi bi-file-earmark-person" aria-hidden="true"></i> Contrataciones</a>
    <?php } : null,
]); ?>

<?php if ($carpetaActual): ?>
  <?php $rutaModuloActual = '/talento-humano'; require ROOT_PATH . '/app/Views/Portal/_shared/_explorador_documental.php'; ?>

<?php else: ?>

  <?php if ($EXPEDIENTES && $terminoBusqueda === ''): ?>
    <div class="section-header"><h2 class="section-heading">Expedientes del personal</h2></div>
    <div class="tile-grid th-expedientes">
      <?php foreach ($EXPEDIENTES as [$ruta, $icono, $label, $meta]): ?>
        <a class="tile" href="<?= BASE_URL . $ruta ?>">
          <span class="th-exp-ic" aria-hidden="true"><i class="bi bi-<?= $icono ?>" aria-hidden="true"></i></span>
          <span class="th-exp-label"><?= e($label) ?></span>
          <span class="th-exp-meta"><?= e($meta) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="filter-bar section-header--spaced">
    <form action="<?= BASE_URL ?>/talento-humano" method="get" class="input-group search-field" role="search" data-no-loading>
      <i class="bi bi-search input-icon" aria-hidden="true"></i>
      <input class="input" type="search" name="buscar" value="<?= e($terminoBusqueda) ?>" placeholder="Buscar hojas de vida, contratos, certificaciones…" aria-label="Buscar documentos de Talento Humano">
    </form>
  </div>

  <?php if ($terminoBusqueda !== ''): ?>

    <div class="section-header">
      <h2 class="section-heading">Resultados para "<?= e($terminoBusqueda) ?>"</h2>
      <a href="<?= BASE_URL ?>/talento-humano" class="btn btn-link btn-sm"><i class="bi bi-x-lg" aria-hidden="true"></i> Limpiar búsqueda</a>
    </div>
    <?php if (!$resultadosBusqueda): ?>
      <?php ui_empty_state(['icon' => 'search', 'title' => 'Sin resultados', 'text' => 'No encontramos ningún documento que coincida con "' . $terminoBusqueda . '".']); ?>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($resultadosBusqueda as $a) { $filaArchivoTH($a, true); } ?>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <div class="section-header"><h2 class="section-heading">Categorías</h2></div>
    <?php if (!$subcarpetas): ?>
      <div class="empty-state">
        <div class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></div>
        <h4>Aún no hay categorías</h4>
        <p>Crea la primera categoría (Hojas de vida, Contratos, Certificaciones laborales…) para empezar a organizar los documentos del personal.</p>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/talento-humano/carpetas/crear" method="post" class="doc-empty-create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <input type="text" name="nombre" placeholder="Nombre de la categoría" aria-label="Nombre de la categoría" required class="input">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear categoría</button>
          </form>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="doc-categorias">
        <?php foreach ($subcarpetas as $c): ?>
          <article class="doc-categoria-card">
            <a href="<?= BASE_URL ?>/talento-humano?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
              <span class="ic" aria-hidden="true"><i class="bi bi-folder-fill" aria-hidden="true"></i></span>
              <span class="nombre"><?= e($c['nombre']) ?></span>
              <span class="meta"><?= (int) $c['total_archivos'] ?> documento<?= ((int) $c['total_archivos'] === 1) ? '' : 's' ?></span>
            </a>
            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/talento-humano/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta categoría?" data-confirm-text="También se eliminará todo lo que tenga dentro." data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger-soft btn-icon doc-cat-borrar" aria-label="Eliminar categoría <?= e($c['nombre']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/talento-humano/carpetas/crear" method="post" class="doc-categoria-card doc-categoria-card--create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <span class="ic" aria-hidden="true"><i class="bi bi-folder-plus" aria-hidden="true"></i></span>
            <input type="text" name="nombre" placeholder="Nueva categoría" aria-label="Nombre de la nueva categoría" required class="input input-sm">
            <button type="submit" class="btn btn-sm">Crear</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="section-header section-header--spaced"><h2 class="section-heading">Archivos recientes</h2></div>
    <?php if (!$archivosRecientes): ?>
      <?php ui_empty_state(['icon' => 'file-earmark-text', 'title' => 'Aún no hay documentos', 'text' => 'Sube el primer archivo dentro de una categoría para verlo acá.', 'compact' => true]); ?>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($archivosRecientes as $a) { $filaArchivoTH($a, false); } ?>
      </div>
    <?php endif; ?>

    <div class="modulo-grid th-grid">
      <section>
        <div class="section-header section-header--spaced"><h2 class="section-heading">Organigrama</h2></div>
        <?php foreach ($organigrama as $nivel): ?>
          <h3 class="th-nivel"><?= e($nivel['label']) ?></h3>
          <div class="th-org">
            <?php foreach ($nivel['cajas'] as $caja): ?>
              <div class="th-org-caja<?= $caja['destacado'] ? ' is-destacado' : '' ?>">
                <span class="th-org-label"><?= e($caja['label']) ?></span>
                <?php if ($caja['meta']): ?><span class="th-org-meta"><?= e($caja['meta']) ?></span><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </section>
      <aside>
        <div class="section-header section-header--spaced"><h2 class="section-heading">Competencias institucionales</h2></div>
        <div class="box-card box-card--flat box-card--compact">
          <?php foreach ($competencias as $c): ?>
            <div class="exec-row">
              <div class="exec-head"><span><?= e($c['label']) ?></span><strong><?= (int) $c['pct'] ?>%</strong></div>
              <div class="progress-track" role="progressbar" aria-valuenow="<?= (int) $c['pct'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($c['label']) ?>"><div class="progress-fill" style="width:<?= (int) $c['pct'] ?>%"></div></div>
            </div>
          <?php endforeach; ?>
        </div>
      </aside>
    </div>

  <?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
