<?php
$titulo = 'Talento Humano';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
$esAdminDoc = ($_SESSION['usuario_rol'] ?? '') === 'admin';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<div class="doc-hero">
  <h1 class="doc-hero-titulo">Talento Humano</h1>
  <p class="doc-hero-desc">Hojas de vida, contratos y certificaciones laborales, organizados en un solo lugar.</p>
</div>

<?php if ($carpetaActual): ?>
  <a href="<?= BASE_URL ?>/talento-humano" class="doc-back">
    <i class="bi bi-arrow-left"></i> Volver a Documentos
  </a>
  <?php $rutaModuloActual = '/talento-humano'; require ROOT_PATH . '/app/Views/Portal/_shared/_explorador_documental.php'; ?>

<?php else: ?>

  <div class="doc-search">
    <form action="<?= BASE_URL ?>/talento-humano" method="get" class="doc-search-form" role="search">
      <i class="bi bi-search"></i>
      <input type="text" name="buscar" value="<?= e($terminoBusqueda) ?>" placeholder="Buscar hojas de vida, contratos, certificaciones...">
    </form>
  </div>

  <?php if ($terminoBusqueda !== ''): ?>

    <div class="doc-section-head"><h4>Resultados para "<?= e($terminoBusqueda) ?>"</h4>
      <a href="<?= BASE_URL ?>/talento-humano" class="doc-clear-search">Limpiar búsqueda</a>
    </div>
    <?php if (!$resultadosBusqueda): ?>
      <div class="empty-state">
        <div class="ic"><i class="bi bi-search"></i></div>
        <h4>Sin resultados</h4>
        <p>No encontramos ningún documento que coincida con "<?= e($terminoBusqueda) ?>".</p>
      </div>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($resultadosBusqueda as $a): ?>
          <div class="doc-archivo-row">
            <span class="ic"><i class="bi bi-file-earmark-text"></i></span>
            <span class="info">
              <span class="nombre"><?= e($a['nombre']) ?></span>
              <span class="meta">
                <?php if (!empty($a['carpeta_nombre'])): ?><span class="doc-tag-origen"><?= e($a['carpeta_nombre']) ?></span><?php endif; ?>
                <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
                <?= e((new DateTime($a['fecha_raw']))->format('d M Y')) ?>
              </span>
            </span>
            <span class="doc-actions">
              <?php $urlVer = BASE_URL . '/talento-humano/carpetas/descargar?id=' . (int) $a['id'];
              $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf'; ?>
              <a class="doc-btn doc-btn--secondary" href="<?= e($urlVer) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <div class="doc-section-head"><h4>Categorías</h4></div>
    <?php if (!$subcarpetas): ?>
      <div class="empty-state">
        <div class="ic"><i class="bi bi-folder2"></i></div>
        <h4>Aún no hay categorías</h4>
        <p>Crea la primera categoría (Hojas de vida, Contratos, Certificaciones laborales...) para empezar a organizar los documentos del personal.</p>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/talento-humano/carpetas/crear" method="post" class="doc-empty-create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <input type="text" name="nombre" placeholder="Nombre de la categoría" required class="doc-input">
            <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-plus-lg"></i> Crear categoría</button>
          </form>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="doc-categorias">
        <?php foreach ($subcarpetas as $c): ?>
          <article class="doc-categoria-card">
            <a href="<?= BASE_URL ?>/talento-humano?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
              <span class="ic"><i class="bi bi-folder-fill"></i></span>
              <span class="nombre"><?= e($c['nombre']) ?></span>
              <span class="meta"><?= (int) $c['total_archivos'] ?> documento<?= ((int) $c['total_archivos'] === 1) ? '' : 's' ?></span>
            </a>
            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/talento-humano/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta categoría y TODO lo que tenga dentro?')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon doc-cat-borrar" aria-label="Eliminar categoría"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/talento-humano/carpetas/crear" method="post" class="doc-categoria-card doc-categoria-card--create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <span class="ic"><i class="bi bi-folder-plus"></i></span>
            <input type="text" name="nombre" placeholder="Nueva categoría" required class="doc-input">
            <button type="submit" class="doc-btn doc-btn--secondary">Crear</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="doc-section-head"><h4>Archivos recientes</h4></div>
    <?php if (!$archivosRecientes): ?>
      <div class="empty-state">
        <div class="ic"><i class="bi bi-file-earmark-text"></i></div>
        <h4>Aún no hay documentos</h4>
        <p>Sube el primer archivo dentro de una categoría para verlo acá.</p>
      </div>
    <?php else: ?>
      <div class="doc-archivos">
        <?php foreach ($archivosRecientes as $a): ?>
          <div class="doc-archivo-row">
            <span class="ic"><i class="bi bi-file-earmark-text"></i></span>
            <span class="info">
              <span class="nombre"><?= e($a['nombre']) ?></span>
              <span class="meta">
                <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
                <?= e((new DateTime($a['fecha_raw']))->format('d M Y')) ?>
              </span>
            </span>
            <span class="doc-actions">
              <?php $urlVer = BASE_URL . '/talento-humano/carpetas/descargar?id=' . (int) $a['id'];
              $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf'; ?>
              <a class="doc-btn doc-btn--secondary" href="<?= e($urlVer) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
              <?php if ($esAdminDoc): ?>
                <form action="<?= BASE_URL ?>/talento-humano/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar este documento?')">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
                  <input type="hidden" name="archivo_id" value="<?= (int) $a['id'] ?>">
                  <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" aria-label="Eliminar documento"><i class="bi bi-trash"></i></button>
                </form>
              <?php endif; ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
