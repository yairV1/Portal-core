<?php
$titulo = 'Investigación e Innovación';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
$esAdminDoc = usuario_admin_de($direccion['id'] ?? null);
$heroTitulo = $moduloTitulo ?: 'Investigación e Innovación';
$heroDesc = $moduloDesc ?: 'Proyectos, publicaciones y convocatorias de investigación, organizados en un solo lugar.';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<div class="doc-hero">
  <?php if ($moduloKicker): ?><div class="modulo-kicker"><?= e($moduloKicker) ?></div><?php endif; ?>
  <h1 class="doc-hero-titulo"><?= e($heroTitulo) ?></h1>
  <p class="doc-hero-desc"><?= e($heroDesc) ?></p>
</div>

<?php if ($carpetaActual): ?>
  <a href="<?= BASE_URL ?>/investigacion-innovacion" class="doc-back">
    <i class="bi bi-arrow-left"></i> Volver a Documentos
  </a>
  <?php $rutaModuloActual = '/investigacion-innovacion'; require ROOT_PATH . '/app/Views/Portal/_shared/_explorador_documental.php'; ?>

<?php else: ?>

  <div class="doc-search">
    <form action="<?= BASE_URL ?>/investigacion-innovacion" method="get" class="doc-search-form" role="search">
      <i class="bi bi-search"></i>
      <input type="text" name="buscar" value="<?= e($terminoBusqueda) ?>" placeholder="Buscar proyectos, publicaciones, convocatorias...">
    </form>
    <?php $rutaModuloActual = '/investigacion-innovacion'; require ROOT_PATH . '/app/Views/Portal/_shared/_agregar_documento.php'; ?>
  </div>

  <?php if ($terminoBusqueda !== ''): ?>

    <div class="doc-section-head"><h4>Resultados para "<?= e($terminoBusqueda) ?>"</h4>
      <a href="<?= BASE_URL ?>/investigacion-innovacion" class="doc-clear-search">Limpiar búsqueda</a>
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
              <?php $urlVer = $a['origen'] === 'carpeta'
                  ? BASE_URL . '/investigacion-innovacion/carpetas/descargar?id=' . (int) $a['id']
                  : BASE_URL . '/documentos/descargar?tipo=direccion&id=' . (int) $a['id'];
              $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf'; ?>
              <a class="doc-btn doc-btn--secondary" href="<?= e($urlVer) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
              <?php if (onlyoffice_configurado() && onlyoffice_editable($a['archivo'])): ?>
                <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= BASE_URL ?>/editor?tipo=<?= $a['origen'] === 'carpeta' ? 'carpeta' : 'direccion' ?>&id=<?= (int) $a['id'] ?>&volver=<?= urlencode(BASE_URL . '/investigacion-innovacion') ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal"><i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>"></i></a>
              <?php endif; ?>
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
        <p>Crea la primera categoría (Proyectos de investigación, Publicaciones, Convocatorias...) para empezar a organizar los documentos.</p>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/investigacion-innovacion/carpetas/crear" method="post" class="doc-empty-create">
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
            <a href="<?= BASE_URL ?>/investigacion-innovacion?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
              <span class="ic"><i class="bi bi-folder-fill"></i></span>
              <span class="nombre"><?= e($c['nombre']) ?></span>
              <span class="meta"><?= (int) $c['total_archivos'] ?> documento<?= ((int) $c['total_archivos'] === 1) ? '' : 's' ?></span>
            </a>
            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/investigacion-innovacion/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta categoría y TODO lo que tenga dentro?')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon doc-cat-borrar" aria-label="Eliminar categoría"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
        <?php if ($esAdminDoc): ?>
          <form action="<?= BASE_URL ?>/investigacion-innovacion/carpetas/crear" method="post" class="doc-categoria-card doc-categoria-card--create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
            <span class="ic"><i class="bi bi-folder-plus"></i></span>
            <input type="text" name="nombre" placeholder="Nueva categoría" required class="doc-input">
            <button type="submit" class="doc-btn doc-btn--secondary">Crear</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="doc-section-head" id="doc-agregar"><h4>Archivos recientes</h4></div>
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
                <span class="doc-tag-origen"><?= ['carpeta' => 'Carpeta', 'documento' => 'Documento', 'formato' => 'Formato'][$a['origen']] ?></span>
                <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
                <?= e((new DateTime($a['fecha_raw']))->format('d M Y')) ?>
              </span>
            </span>
            <span class="doc-actions">
              <?php $urlVer = $a['origen'] === 'carpeta'
                  ? BASE_URL . '/investigacion-innovacion/carpetas/descargar?id=' . (int) $a['id']
                  : BASE_URL . '/documentos/descargar?tipo=direccion&id=' . (int) $a['id'];
              $esPdf = strtolower(pathinfo($a['archivo'], PATHINFO_EXTENSION)) === 'pdf'; ?>
              <a class="doc-btn doc-btn--secondary" href="<?= e($urlVer) ?>" target="_blank" rel="noopener"><i class="bi <?= $esPdf ? 'bi-eye' : 'bi-download' ?>"></i> <?= $esPdf ? 'Ver' : 'Descargar' ?></a>
              <?php if (onlyoffice_configurado() && onlyoffice_editable($a['archivo'])): ?>
                <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= BASE_URL ?>/editor?tipo=<?= $a['origen'] === 'carpeta' ? 'carpeta' : 'direccion' ?>&id=<?= (int) $a['id'] ?>&volver=<?= urlencode(BASE_URL . '/investigacion-innovacion') ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal"><i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>"></i></a>
              <?php endif; ?>
              <?php if ($esAdminDoc && $a['origen'] === 'carpeta'): ?>
                <form action="<?= BASE_URL ?>/investigacion-innovacion/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar este documento?')">
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

    <?php
    $pendientes = array_filter(array_merge(
        array_map(fn ($d) => $d + ['tipoEtq' => 'Documento'], $moduloDocumentos),
        array_map(fn ($f) => $f + ['tipoEtq' => 'Formato'], $moduloFormatos)
    ), fn ($d) => !$d['archivo']);
    ?>
    <?php if ($esAdminDoc): ?>
      <?php if ($pendientes): ?>
        <div class="doc-section-head doc-section-head--spaced"><h4>Pendientes de adjuntar</h4><span class="text-muted">Ya tienen nombre, les falta el archivo</span></div>
        <div class="doc-archivos">
          <?php foreach ($pendientes as $p): ?>
            <div class="doc-archivo-row">
              <span class="ic"><i class="bi bi-file-earmark-arrow-up"></i></span>
              <span class="info">
                <span class="nombre"><?= e($p['nombre']) ?></span>
                <span class="meta"><span class="doc-tag-origen"><?= e($p['tipoEtq']) ?></span></span>
              </span>
              <span class="doc-actions">
                <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="tipo" value="direccion">
                  <input type="hidden" name="volver" value="/investigacion-innovacion">
                  <input type="hidden" name="archivo_id" value="<?= (int) $p['id'] ?>">
                  <label class="file-btn doc-btn doc-btn--primary">
                    <i class="bi bi-upload"></i> Subir
                    <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required onchange="this.form.submit()">
                  </label>
                </form>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  <?php endif; ?>

  <div class="section-head doc-section-head--spaced"><h4>Indicadores</h4></div>
  <div class="modulo-kpis doc-kpis" id="moduloKpis">
    <?php if (!$moduloKpis): ?>
      <div class="modulo-vacio"><i class="bi bi-bar-chart-line"></i> Sin indicadores por ahora.</div>
    <?php else: foreach ($moduloKpis as $k): ?>
      <div class="modulo-kpi">
        <div class="label"><?= e($k['label']) ?></div>
        <div class="valor"><?= e($k['valor']) ?></div>
        <span class="spark" data-valor="<?= e($k['valor']) ?>"></span>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="modulo-grid">
    <div>
      <div class="section-head"><h4>Áreas del módulo</h4></div>
      <div id="moduloAreas">
        <?php if (!$moduloAreas): ?>
          <div class="modulo-vacio"><i class="bi bi-folder2"></i> Sin áreas registradas por ahora.</div>
        <?php else: foreach ($moduloAreas as $a): ?>
          <div class="area-item" id="<?= e(sb_slug($a['label'])) ?>">
            <span class="ic"><i class="bi bi-folder2"></i></span>
            <span class="doc-flex-grow">
              <span class="label"><?= e($a['label']) ?></span>
              <span class="meta"><?= e($a['meta']) ?></span>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="doc-side-stack">
      <div class="side-box">
        <div class="side-box-title">Responsables</div>
        <div id="moduloResponsables">
          <?php if (!$moduloResponsables): ?>
            <div class="modulo-vacio"><i class="bi bi-people"></i> Sin responsables por ahora.</div>
          <?php else: foreach ($moduloResponsables as $r):
              $partesNombre = preg_split('/\s+/', trim($r['nombre']));
              $iniciales = mb_strtoupper(mb_substr($partesNombre[0], 0, 1) . mb_substr(end($partesNombre), 0, 1));
          ?>
            <div class="responsable">
              <span class="ini"><?= e($iniciales) ?></span>
              <span class="doc-flex-grow">
                <span class="nombre"><?= e($r['nombre']) ?></span>
                <span class="cargo"><?= e($r['cargo']) ?></span>
              </span>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <div class="side-box">
        <div class="side-box-title">Software relacionado</div>
        <div id="moduloSoftware">
          <?php if (!$moduloSoftware): ?>
            <div class="modulo-vacio"><i class="bi bi-link-45deg"></i> Sin software relacionado.</div>
          <?php else: foreach ($moduloSoftware as $s): ?>
            <div class="software-item"><span class="doc-soft-icon"><i class="bi bi-link-45deg"></i></span><?= e($s['nombre']) ?></div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>

<?php endif; ?>

<script src="<?= BASE_URL ?>/assets/portal/js/investigacion-innovacion.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
