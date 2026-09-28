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

<?php
$ESTADO_TAG = ['Vigente' => 'success', 'En revisión' => 'warning', 'Obsoleto' => 'danger'];
// Los documentos solo cuelgan del nivel de "área" (hoja), nunca del de
// "dirección" (ver PortalController.php) — "Agregar documento" solo aparece
// dentro de un área.
$estaEnAreaDoc = $carpetaActualDoc && $carpetaActualDoc['parent_id'] !== null;
$badgeVisibilidad = function (string $vis, string $fem = 'a') {
    $pub = $vis === 'publico';
    ?><span class="badge <?= $pub ? 'badge-success' : 'badge-neutral' ?> gd-badge"><i class="bi <?= $pub ? 'bi-globe2' : 'bi-lock-fill' ?>" aria-hidden="true"></i> <?= $pub ? 'Públic' . $fem : 'Privad' . $fem ?></span><?php
};
if ($carpetaActualDoc) {
    $volverAId = $carpetaActualDoc['parent_id'] ? (int) $carpetaActualDoc['parent_id'] : null;
    $volverAUrl = BASE_URL . '/gestion-documental' . ($volverAId ? '?carpeta=' . $volverAId : '');
}
ui_page_header([
    'title'   => $carpetaActualDoc ? $carpetaActualDoc['label'] : 'Gestión Documental',
    'desc'    => $carpetaActualDoc ? null : 'Repositorio institucional de documentos, formatos y control de versiones — solo se muestra lo marcado como público.',
    'eyebrow' => $carpetaActualDoc ? 'Gestión Documental' : null,
    'back'    => $carpetaActualDoc ? ['href' => $volverAUrl, 'label' => 'Volver'] : null,
    'actions' => $carpetaActualDoc ? function () use ($carpetaActualDoc, $esAdminDoc, $estaEnAreaDoc, $csrf, $badgeVisibilidad) { ?>
        <?php $badgeVisibilidad($carpetaActualDoc['visibilidad']); ?>
        <?php if ($esAdminDoc): $pub = $carpetaActualDoc['visibilidad'] === 'publico'; ?>
          <form action="<?= BASE_URL ?>/gestion-documental/carpetas/visibilidad" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
            <button type="submit" class="btn btn-sm"><i class="bi <?= $pub ? 'bi-lock' : 'bi-globe2' ?>" aria-hidden="true"></i> <?= $pub ? 'Hacer privada' : 'Hacer pública' ?></button>
          </form>
          <button type="button" class="btn btn-sm btn-icon" aria-label="Renombrar carpeta" title="Renombrar carpeta"
                  onclick='gdEditarCarpeta(<?= (int) $carpetaActualDoc['id'] ?>, <?= json_encode($carpetaActualDoc['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
          </button>
          <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta carpeta?" data-confirm-text="Dejará de verse, junto con lo que tenga dentro." data-confirm-ok="Eliminar">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger-soft btn-icon" aria-label="Eliminar carpeta" title="Eliminar carpeta"><i class="bi bi-trash" aria-hidden="true"></i></button>
          </form>
          <?php if ($estaEnAreaDoc): ?>
            <button type="button" class="btn btn-sm btn-primary" data-open="gdAgregarDoc" aria-haspopup="dialog"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
          <?php endif; ?>
        <?php endif; ?>
    <?php } : null,
]);
?>

<?php if (!$carpetaActualDoc): ?>
  <div class="section-header"><h2 class="section-heading"><i class="bi bi-archive" aria-hidden="true"></i> Repositorio institucional</h2></div>
<?php endif; ?>

<?php if (!$subcarpetasDoc && !$archivosDoc): ?>
  <?php ui_empty_state([
      'icon'  => 'folder2-open',
      'title' => 'Todavía no hay nada público acá',
      'text'  => $esAdminDoc ? 'Usa "Hacer pública" en una carpeta para que empiece a mostrarse.' : 'Vuelve pronto: el administrador todavía no ha publicado documentos.',
  ]); ?>
<?php else: ?>

  <?php if ($subcarpetasDoc): ?>
    <div class="doc-categorias">
      <?php foreach ($subcarpetasDoc as $c): $totalHijos = $conteosDoc[$c['id']] ?? 0; ?>
        <article class="doc-categoria-card">
          <a href="<?= BASE_URL ?>/gestion-documental?carpeta=<?= (int) $c['id'] ?>" class="doc-categoria-link">
            <span class="ic"><i class="bi bi-folder-fill" aria-hidden="true"></i></span>
            <span class="nombre">
              <?= e($c['label']) ?>
              <?php $badgeVisibilidad($c['visibilidad']); ?>
            </span>
            <span class="meta"><?= $totalHijos ?> <?= $carpetaActualDoc ? ($totalHijos === 1 ? 'documento' : 'documentos') : ($totalHijos === 1 ? 'área' : 'áreas') ?></span>
          </a>
          <?php if ($esAdminDoc): ?>
            <span class="gd-actions gd-categoria-actions">
              <form action="<?= BASE_URL ?>/gestion-documental/carpetas/visibilidad" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm btn-icon" title="<?= $c['visibilidad'] === 'publico' ? 'Hacer privada' : 'Hacer pública' ?>">
                  <i class="bi <?= $c['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>" aria-hidden="true"></i>
                </button>
              </form>
              <button type="button" class="btn btn-ghost btn-sm btn-icon" title="Renombrar" aria-label="Renombrar"
                      onclick='gdEditarCarpeta(<?= (int) $c['id'] ?>, <?= json_encode($c['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
              </button>
              <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" data-confirm="¿Eliminar esta carpeta?" data-confirm-text="Dejará de verse, junto con lo que tenga dentro." data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger-soft btn-icon" title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>
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
          <span class="ic"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
          <span class="info">
            <span class="nombre"><?= e($arc['nombre']) ?></span>
            <span class="meta">
              <?php $badgeVisibilidad($arc['visibilidad'], 'o'); ?>
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
              <a class="btn btn-ghost btn-sm btn-icon" href="<?= BASE_URL ?>/documentos/descargar?tipo=documental&id=<?= (int) $arc['id'] ?>" target="_blank" rel="noopener" title="<?= $esPdfDoc ? 'Ver' : 'Descargar' ?>">
                <i class="bi <?= $esPdfDoc ? 'bi-eye' : 'bi-download' ?>" aria-hidden="true"></i>
              </a>
              <?php if (onlyoffice_configurado() && onlyoffice_editable($arc['archivo'])): ?>
                <a class="btn btn-ghost btn-sm btn-icon" href="<?= BASE_URL ?>/editor?tipo=documental&id=<?= (int) $arc['id'] ?>&volver=<?= urlencode(BASE_URL . '/gestion-documental?carpeta=' . (int) $carpetaActualDoc['id']) ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal">
                  <i class="bi <?= $esAdminDoc ? 'bi-pencil-square' : 'bi-eye' ?>" aria-hidden="true"></i>
                </a>
              <?php endif; ?>
            <?php elseif ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="tipo" value="documental">
                <input type="hidden" name="volver" value="/gestion-documental">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <label class="file-btn btn btn-sm btn-primary"><i class="bi bi-upload" aria-hidden="true"></i> Subir
                  <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required onchange="this.form.submit()" aria-label="Subir archivo para <?= e($arc['nombre']) ?>">
                </label>
              </form>
            <?php else: ?>
              <span class="badge badge-neutral">Sin archivo</span>
            <?php endif; ?>

            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/documentos/visibilidad" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="btn btn-ghost btn-sm btn-icon" title="<?= $arc['visibilidad'] === 'publico' ? 'Hacer privado' : 'Hacer público' ?>">
                  <i class="bi <?= $arc['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>" aria-hidden="true"></i>
                </button>
              </form>
              <button type="button" class="btn btn-ghost btn-sm btn-icon" title="Editar" aria-label="Editar"
                      onclick='gdEditarDoc(<?= (int) $arc["id"] ?>, <?= json_encode($arc["nombre"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["tipo"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["version"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["responsable"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["fecha"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
              </button>
              <form action="<?= BASE_URL ?>/documentos/eliminar" method="post" data-confirm="¿Eliminar este documento?" data-confirm-ok="Eliminar">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="btn btn-sm btn-danger-soft btn-icon" title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>
              </form>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php if ($esAdminDoc && $estaEnAreaDoc): ?>
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
