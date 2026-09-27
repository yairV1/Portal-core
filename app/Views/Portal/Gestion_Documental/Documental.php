<?php
// Repositorio institucional público (ver PortalController.php
// '/gestion-documental' y migración 053_gestion_documental_publico.sql).
// El admin global ve TODO lo activo (público y privado, con badge) para
// poder curar qué se muestra; cualquier otro rol solo ve lo público y
// activo. "Mi Google Drive" (Drive personal) tiene su propia página, ver
// /mi-drive (DriveUsuarioController.php + Views/Portal/Mi_Drive/MiDrive.php)
// — esta vista es solo el repositorio institucional, nada personal/privado.
$titulo = 'Gestión Documental';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/gestion-documental.css') ?>">

<h1 class="page-title">Gestión Documental</h1>
<p class="page-desc">Repositorio institucional de documentos, formatos y control de versiones — solo se muestra lo marcado como público.</p>

<?php $ESTADO_TAG = ['Vigente' => 'success', 'En revisión' => 'warning', 'Obsoleto' => 'danger']; ?>

<?php if ($carpetaActualDoc):
  $volverAId = $carpetaActualDoc['parent_id'] ? (int) $carpetaActualDoc['parent_id'] : null;
  $volverAUrl = BASE_URL . '/gestion-documental' . ($volverAId ? '?carpeta=' . $volverAId : '');
?>
  <a href="<?= e($volverAUrl) ?>" class="doc-back"><i class="bi bi-arrow-left"></i> Volver</a>

  <div class="gd-folder-head" style="margin-bottom:18px">
    <h4 style="margin:0"><i class="bi bi-folder-fill" style="color:var(--color-warning)"></i> <?= e($carpetaActualDoc['label']) ?></h4>
    <span class="badge <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'badge-success' : 'badge-outline' ?>">
      <i class="bi <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'bi-globe2' : 'bi-lock-fill' ?>"></i>
      <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'Pública' : 'Privada' ?>
    </span>
    <?php if ($esAdminDoc): ?>
      <span class="gd-actions">
        <form action="<?= BASE_URL ?>/gestion-documental/carpetas/visibilidad" method="post">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
          <button type="submit" class="btn btn-icon" title="<?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'Hacer privada' : 'Hacer pública' ?>">
            <i class="bi <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
          </button>
        </form>
        <button type="button" class="btn btn-icon" title="Renombrar carpeta"
                onclick='gdEditarCarpeta(<?= (int) $carpetaActualDoc['id'] ?>, <?= json_encode($carpetaActualDoc['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
          <i class="bi bi-pencil-square"></i>
        </button>
        <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta carpeta?" data-confirm-text="Dejará de verse, junto con lo que tenga dentro." data-confirm-ok="Eliminar">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
          <button type="submit" class="btn btn-danger-soft btn-icon" title="Eliminar carpeta"><i class="bi bi-trash"></i></button>
        </form>
      </span>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="section-header" style="margin:0 0 14px"><h2 class="section-heading"><i class="bi bi-archive"></i> Repositorio institucional</h2></div>
<?php endif; ?>

<?php if (!$subcarpetasDoc && !$archivosDoc): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-folder2-open"></i></div>
    <h4>Todavía no hay nada público acá</h4>
    <p><?= $esAdminDoc
        ? 'Marca una carpeta como pública (icono <i class="bi bi-globe2"></i>) para que empiece a mostrarse.'
        : 'Vuelve pronto — el administrador todavía no ha publicado documentos.' ?></p>
  </div>
<?php else: ?>

  <?php if ($subcarpetasDoc): ?>
    <div class="doc-categorias">
      <?php foreach ($subcarpetasDoc as $c): $totalHijos = $conteosDoc[$c['id']] ?? 0; ?>
        <article class="doc-categoria-card">
          <a href="<?= BASE_URL ?>/gestion-documental?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
            <span class="ic"><i class="bi bi-folder-fill"></i></span>
            <span class="nombre">
              <?= e($c['label']) ?>
              <span class="badge <?= $c['visibilidad'] === 'publico' ? 'badge-success' : 'badge-outline' ?> gd-badge">
                <?= $c['visibilidad'] === 'publico' ? 'Pública' : 'Privada' ?>
              </span>
            </span>
            <span class="meta"><?= $totalHijos ?> <?= $carpetaActualDoc ? ($totalHijos === 1 ? 'documento' : 'documentos') : ($totalHijos === 1 ? 'área' : 'áreas') ?></span>
          </a>
          <?php if ($esAdminDoc): ?>
            <span class="gd-actions gd-categoria-actions">
              <form action="<?= BASE_URL ?>/gestion-documental/carpetas/visibilidad" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-icon" title="<?= $c['visibilidad'] === 'publico' ? 'Hacer privada' : 'Hacer pública' ?>">
                  <i class="bi <?= $c['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
                </button>
              </form>
              <button type="button" class="btn btn-icon" title="Renombrar"
                      onclick='gdEditarCarpeta(<?= (int) $c['id'] ?>, <?= json_encode($c['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square"></i>
              </button>
              <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta carpeta?" data-confirm-text="Dejará de verse, junto con lo que tenga dentro." data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-danger-soft btn-icon" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            </span>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($archivosDoc): ?>
    <div class="section-header<?= $subcarpetasDoc ? ' section-header--spaced' : '' ?>"><h2 class="section-heading">Documentos</h2></div>
    <div class="doc-archivos">
      <?php foreach ($archivosDoc as $arc): ?>
        <div class="doc-archivo-row">
          <span class="ic"><i class="bi bi-file-earmark-text"></i></span>
          <span class="info">
            <span class="nombre"><?= e($arc['nombre']) ?></span>
            <span class="meta">
              <span class="badge <?= $arc['visibilidad'] === 'publico' ? 'badge-success' : 'badge-outline' ?> gd-badge">
                <?= $arc['visibilidad'] === 'publico' ? 'Pública' : 'Privada' ?>
              </span>
              <?php if ($arc['tipo']): ?><span class="doc-tag-origen"><?= e($arc['tipo']) ?></span><?php endif; ?>
              <span class="tag-stamp"><?= e($arc['version']) ?></span>
              <span class="badge badge-<?= e($ESTADO_TAG[$arc['estado']] ?? 'info') ?>"><?= e($arc['estado']) ?></span>
              <?= $arc['fecha'] ? e((new DateTime($arc['fecha']))->format('d M Y')) : '' ?>
            </span>
          </span>
          <span class="doc-actions gd-actions">
            <?php if ($arc['archivo']):
              $esPdfDoc = strtolower(pathinfo($arc['archivo'], PATHINFO_EXTENSION)) === 'pdf';
            ?>
              <a class="btn btn-icon" href="<?= BASE_URL ?>/documentos/descargar?tipo=documental&id=<?= (int) $arc['id'] ?>" target="_blank" rel="noopener" title="<?= $esPdfDoc ? 'Ver' : 'Descargar' ?>">
                <i class="bi <?= $esPdfDoc ? 'bi-eye' : 'bi-download' ?>"></i>
              </a>
              <?php if (onlyoffice_configurado() && onlyoffice_editable($arc['archivo'])): ?>
                <a class="btn btn-icon" href="<?= BASE_URL ?>/editor?tipo=documental&id=<?= (int) $arc['id'] ?>&volver=<?= urlencode(BASE_URL . '/gestion-documental?carpeta=' . (int) $carpetaActualDoc['id']) ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal">
                  <i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>"></i>
                </a>
              <?php endif; ?>
            <?php elseif ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data" class="gd-upload-inline">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="tipo" value="documental">
                <input type="hidden" name="volver" value="/gestion-documental">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>
                <button type="submit" class="btn btn-icon" title="Subir archivo"><i class="bi bi-upload"></i></button>
              </form>
            <?php else: ?>
              <span class="text-muted" style="font-size:11.5px">Sin archivo</span>
            <?php endif; ?>

            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/documentos/visibilidad" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="btn btn-icon" title="<?= $arc['visibilidad'] === 'publico' ? 'Hacer privado' : 'Hacer público' ?>">
                  <i class="bi <?= $arc['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
                </button>
              </form>
              <button type="button" class="btn btn-icon" title="Editar"
                      onclick='gdEditarDoc(<?= (int) $arc["id"] ?>, <?= json_encode($arc["nombre"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["tipo"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["version"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["responsable"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["fecha"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square"></i>
              </button>
              <form action="<?= BASE_URL ?>/documentos/eliminar" method="post" data-confirm="¿Eliminar este documento?" data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="btn btn-danger-soft btn-icon" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php // Los documentos solo cuelgan del nivel de "área" (hoja), nunca del de
      // "dirección" (ver el comentario del propio PortalController.php) —
      // sin esto, "Agregar documento" también aparecía dentro de una
      // dirección y creaba filas con carpeta_id de dirección, mezcladas con
      // las tarjetas de área al listar.
      $estaEnAreaDoc = $carpetaActualDoc && $carpetaActualDoc['parent_id'] !== null;
?>
<?php if ($esAdminDoc && $estaEnAreaDoc): ?>
  <div class="page-actions gd-agregar">
    <button type="button" class="btn btn-primary" data-open="gdAgregarDoc" aria-haspopup="dialog">
      <i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento
    </button>
  </div>

  <dialog id="gdAgregarDoc" class="modal" aria-labelledby="gdAgregarDocT">
    <form action="<?= BASE_URL ?>/documentos/crear" method="post">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="tipo" value="documental">
      <input type="hidden" name="volver" value="/gestion-documental">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
      <header class="modal-header">
        <div class="modal-heading">
          <h2 class="modal-title" id="gdAgregarDocT">Agregar documento</h2>
          <p class="modal-desc">Después de crearlo podrás adjuntarle el archivo desde la lista.</p>
        </div>
        <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
      </header>
      <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="gdAgregarDocNombre">Nombre <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" name="nombre" id="gdAgregarDocNombre" required placeholder="Ej. Procedimiento de compras">
      </div>
      <div class="form-grid">
        <div class="field">
          <label class="field-label" for="gdAgregarDocTipo">Tipo <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="tipo_doc" id="gdAgregarDocTipo" placeholder="Ej. Formato">
        </div>
        <div class="field">
          <label class="field-label" for="gdAgregarDocVersion">Versión <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="version" id="gdAgregarDocVersion" placeholder="v1.0">
        </div>
        <div class="field">
          <label class="field-label" for="gdAgregarDocResponsable">Responsable <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="responsable" id="gdAgregarDocResponsable" placeholder="Nombre de quien lo gestiona">
        </div>
        <div class="field">
          <label class="field-label" for="gdAgregarDocFecha">Fecha</label>
          <input class="input" type="date" name="fecha" id="gdAgregarDocFecha" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
      </footer>
    </form>
  </dialog>
<?php endif; ?>

<?php if ($esAdminDoc): ?>
  <dialog id="gdEditarDocDialog" class="modal" aria-labelledby="gdEditarDocT">
    <form action="<?= BASE_URL ?>/documentos/editar" method="post">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="volver" value="/gestion-documental">
      <input type="hidden" name="archivo_id" id="gdEditarDocId">
      <header class="modal-header">
        <div class="modal-heading">
          <h2 class="modal-title" id="gdEditarDocT">Editar documento</h2>
          <p class="modal-desc">Actualiza los datos del documento. El archivo adjunto no cambia.</p>
        </div>
        <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
      </header>
      <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="gdEditarDocNombre">Nombre <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" name="nombre" id="gdEditarDocNombre" required placeholder="Ej. Procedimiento de compras">
      </div>
      <div class="form-grid">
        <div class="field">
          <label class="field-label" for="gdEditarDocTipo">Tipo <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="tipo_doc" id="gdEditarDocTipo" placeholder="Ej. Formato">
        </div>
        <div class="field">
          <label class="field-label" for="gdEditarDocVersion">Versión <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="version" id="gdEditarDocVersion" placeholder="v1.0">
        </div>
        <div class="field">
          <label class="field-label" for="gdEditarDocResponsable">Responsable <span class="opt">(opcional)</span></label>
          <input class="input" type="text" name="responsable" id="gdEditarDocResponsable" placeholder="Nombre de quien lo gestiona">
        </div>
        <div class="field">
          <label class="field-label" for="gdEditarDocFecha">Fecha</label>
          <input class="input" type="date" name="fecha" id="gdEditarDocFecha">
        </div>
      </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar cambios</button>
      </footer>
    </form>
  </dialog>

  <dialog id="gdEditarCarpetaDialog" class="modal modal-sm" aria-labelledby="gdEditarCarpetaT">
    <form action="<?= BASE_URL ?>/gestion-documental/carpetas/editar" method="post">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="carpeta_id" id="gdEditarCarpetaId">
      <header class="modal-header">
        <div class="modal-heading">
          <h2 class="modal-title" id="gdEditarCarpetaT">Renombrar carpeta</h2>
        </div>
        <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
      </header>
      <div class="modal-body">
        <div class="field">
          <label class="field-label" for="gdEditarCarpetaNombre">Nombre de la carpeta <span class="req" aria-hidden="true">*</span></label>
          <input class="input" type="text" name="nombre" id="gdEditarCarpetaNombre" required>
        </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar</button>
      </footer>
    </form>
  </dialog>

  <script>
    // Llenan el modal de edición con los datos de la fila y lo abren con
    // el componente del sistema (core/ui.js: foco, Esc, overlay, animación
    // y devolución del foco al botón al cerrar).
    function gdEditarDoc(id, nombre, tipo, version, responsable, fecha) {
      document.getElementById('gdEditarDocId').value = id;
      document.getElementById('gdEditarDocNombre').value = nombre || '';
      document.getElementById('gdEditarDocTipo').value = tipo || '';
      document.getElementById('gdEditarDocVersion').value = version || '';
      document.getElementById('gdEditarDocResponsable').value = responsable || '';
      document.getElementById('gdEditarDocFecha').value = fecha || '';
      UI.open('gdEditarDocDialog', document.activeElement);
    }
    function gdEditarCarpeta(id, nombre) {
      document.getElementById('gdEditarCarpetaId').value = id;
      document.getElementById('gdEditarCarpetaNombre').value = nombre || '';
      UI.open('gdEditarCarpetaDialog', document.activeElement);
    }
  </script>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
