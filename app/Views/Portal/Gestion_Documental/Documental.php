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
    <span class="tag <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'tag-success' : 'tag-outline' ?>">
      <i class="bi <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'bi-globe2' : 'bi-lock-fill' ?>"></i>
      <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'Pública' : 'Privada' ?>
    </span>
    <?php if ($esAdminDoc): ?>
      <span class="gd-actions">
        <form action="<?= BASE_URL ?>/gestion-documental/carpetas/visibilidad" method="post">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
          <button type="submit" class="doc-btn doc-btn--secondary doc-btn--icon" title="<?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'Hacer privada' : 'Hacer pública' ?>">
            <i class="bi <?= $carpetaActualDoc['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
          </button>
        </form>
        <button type="button" class="doc-btn doc-btn--secondary doc-btn--icon" title="Renombrar carpeta"
                onclick='gdEditarCarpeta(<?= (int) $carpetaActualDoc['id'] ?>, <?= json_encode($carpetaActualDoc['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
          <i class="bi bi-pencil-square"></i>
        </button>
        <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta carpeta? Dejará de verse, junto con lo que tenga dentro.')">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
          <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" title="Eliminar carpeta"><i class="bi bi-trash"></i></button>
        </form>
      </span>
    <?php endif; ?>
  </div>
<?php else: ?>
  <!-- .section-head (modulo-generico.css/inicio.css) no está cargado en esta
       vista — quedaba sin ningún estilo. .doc-section-head sí viene de
       centro-documental.css (ya enlazado arriba) y es el mismo que usa el
       encabezado "Documentos" más abajo: mismo look en las dos secciones
       de esta página. -->
  <div class="doc-section-head" style="margin:0 0 14px"><h4><i class="bi bi-archive"></i> Repositorio institucional</h4></div>
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
              <span class="tag <?= $c['visibilidad'] === 'publico' ? 'tag-success' : 'tag-outline' ?> gd-badge">
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
                <button type="submit" class="doc-btn doc-btn--secondary doc-btn--icon" title="<?= $c['visibilidad'] === 'publico' ? 'Hacer privada' : 'Hacer pública' ?>">
                  <i class="bi <?= $c['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
                </button>
              </form>
              <button type="button" class="doc-btn doc-btn--secondary doc-btn--icon" title="Renombrar"
                      onclick='gdEditarCarpeta(<?= (int) $c['id'] ?>, <?= json_encode($c['label'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square"></i>
              </button>
              <form action="<?= BASE_URL ?>/gestion-documental/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar esta carpeta? Dejará de verse, junto con lo que tenga dentro.')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="carpeta_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            </span>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($archivosDoc): ?>
    <div class="doc-section-head<?= $subcarpetasDoc ? ' doc-section-head--spaced' : '' ?>"><h4>Documentos</h4></div>
    <div class="doc-archivos">
      <?php foreach ($archivosDoc as $arc): ?>
        <div class="doc-archivo-row">
          <span class="ic"><i class="bi bi-file-earmark-text"></i></span>
          <span class="info">
            <span class="nombre"><?= e($arc['nombre']) ?></span>
            <span class="meta">
              <span class="tag <?= $arc['visibilidad'] === 'publico' ? 'tag-success' : 'tag-outline' ?> gd-badge">
                <?= $arc['visibilidad'] === 'publico' ? 'Pública' : 'Privada' ?>
              </span>
              <?php if ($arc['tipo']): ?><span class="doc-tag-origen"><?= e($arc['tipo']) ?></span><?php endif; ?>
              <span class="tag-stamp"><?= e($arc['version']) ?></span>
              <span class="tag tag-<?= e($ESTADO_TAG[$arc['estado']] ?? 'info') ?>"><?= e($arc['estado']) ?></span>
              <?= $arc['fecha'] ? e((new DateTime($arc['fecha']))->format('d M Y')) : '' ?>
            </span>
          </span>
          <span class="doc-actions gd-actions">
            <?php if ($arc['archivo']):
              $esPdfDoc = strtolower(pathinfo($arc['archivo'], PATHINFO_EXTENSION)) === 'pdf';
            ?>
              <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= BASE_URL ?>/documentos/descargar?tipo=documental&id=<?= (int) $arc['id'] ?>" target="_blank" rel="noopener" title="<?= $esPdfDoc ? 'Ver' : 'Descargar' ?>">
                <i class="bi <?= $esPdfDoc ? 'bi-eye' : 'bi-download' ?>"></i>
              </a>
              <?php if (onlyoffice_configurado() && onlyoffice_editable($arc['archivo'])): ?>
                <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= BASE_URL ?>/editor?tipo=documental&id=<?= (int) $arc['id'] ?>&volver=<?= urlencode(BASE_URL . '/gestion-documental?carpeta=' . (int) $carpetaActualDoc['id']) ?>" title="<?= $esAdminDoc ? 'Editar' : 'Abrir' ?> dentro del portal">
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
                <button type="submit" class="doc-btn doc-btn--secondary doc-btn--icon" title="Subir archivo"><i class="bi bi-upload"></i></button>
              </form>
            <?php else: ?>
              <span class="text-muted" style="font-size:11.5px">Sin archivo</span>
            <?php endif; ?>

            <?php if ($esAdminDoc): ?>
              <form action="<?= BASE_URL ?>/documentos/visibilidad" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="doc-btn doc-btn--secondary doc-btn--icon" title="<?= $arc['visibilidad'] === 'publico' ? 'Hacer privado' : 'Hacer público' ?>">
                  <i class="bi <?= $arc['visibilidad'] === 'publico' ? 'bi-lock' : 'bi-globe2' ?>"></i>
                </button>
              </form>
              <button type="button" class="doc-btn doc-btn--secondary doc-btn--icon" title="Editar"
                      onclick='gdEditarDoc(<?= (int) $arc["id"] ?>, <?= json_encode($arc["nombre"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["tipo"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["version"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["responsable"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($arc["fecha"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                <i class="bi bi-pencil-square"></i>
              </button>
              <form action="<?= BASE_URL ?>/documentos/eliminar" method="post" onsubmit="return confirm('¿Eliminar este documento?')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
                <input type="hidden" name="volver" value="/gestion-documental">
                <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" title="Eliminar"><i class="bi bi-trash"></i></button>
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
  <div style="margin-top:20px">
    <button type="button" class="doc-btn doc-btn--primary doc-modal-trigger" onclick="document.getElementById('gdAgregarDoc').showModal()">
      <i class="bi bi-plus-lg"></i> Agregar documento
    </button>
  </div>

  <dialog id="gdAgregarDoc" class="doc-modal">
    <div class="doc-modal-body">
      <h4>Agregar documento</h4>
      <form action="<?= BASE_URL ?>/documentos/crear" method="post" style="display:flex;flex-direction:column;gap:10px">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="tipo" value="documental">
        <input type="hidden" name="volver" value="/gestion-documental">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActualDoc['id'] ?>">
        <input type="text" name="nombre" placeholder="Nombre del documento" required class="doc-input">
        <input type="text" name="tipo_doc" placeholder="Tipo (ej: Formato)" class="doc-input">
        <input type="text" name="version" placeholder="v1.0" class="doc-input">
        <input type="text" name="responsable" placeholder="Responsable" class="doc-input">
        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" class="doc-input">
        <p class="text-muted" style="margin:0;font-size:11.5px">Después de crearlo podrás adjuntarle el archivo desde la lista.</p>
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-plus-lg"></i> Agregar</button>
        </div>
      </form>
    </div>
  </dialog>
<?php endif; ?>

<?php if ($esAdminDoc): ?>
  <dialog id="gdEditarDocDialog" class="doc-modal">
    <div class="doc-modal-body">
      <h4>Editar documento</h4>
      <form action="<?= BASE_URL ?>/documentos/editar" method="post" style="display:flex;flex-direction:column;gap:10px">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="volver" value="/gestion-documental">
        <input type="hidden" name="archivo_id" id="gdEditarDocId">
        <input type="text" name="nombre" id="gdEditarDocNombre" placeholder="Nombre" required class="doc-input">
        <input type="text" name="tipo_doc" id="gdEditarDocTipo" placeholder="Tipo" class="doc-input">
        <input type="text" name="version" id="gdEditarDocVersion" placeholder="v1.0" class="doc-input">
        <input type="text" name="responsable" id="gdEditarDocResponsable" placeholder="Responsable" class="doc-input">
        <input type="date" name="fecha" id="gdEditarDocFecha" class="doc-input">
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-check-lg"></i> Guardar</button>
        </div>
      </form>
    </div>
  </dialog>

  <dialog id="gdEditarCarpetaDialog" class="doc-modal">
    <div class="doc-modal-body">
      <h4>Renombrar carpeta</h4>
      <form action="<?= BASE_URL ?>/gestion-documental/carpetas/editar" method="post" style="display:flex;flex-direction:column;gap:10px">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="carpeta_id" id="gdEditarCarpetaId">
        <input type="text" name="nombre" id="gdEditarCarpetaNombre" placeholder="Nombre de la carpeta" required class="doc-input">
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-check-lg"></i> Guardar</button>
        </div>
      </form>
    </div>
  </dialog>

  <script>
    function gdEditarDoc(id, nombre, tipo, version, responsable, fecha) {
      document.getElementById('gdEditarDocId').value = id;
      document.getElementById('gdEditarDocNombre').value = nombre || '';
      document.getElementById('gdEditarDocTipo').value = tipo || '';
      document.getElementById('gdEditarDocVersion').value = version || '';
      document.getElementById('gdEditarDocResponsable').value = responsable || '';
      document.getElementById('gdEditarDocFecha').value = fecha || '';
      document.getElementById('gdEditarDocDialog').showModal();
    }
    function gdEditarCarpeta(id, nombre) {
      document.getElementById('gdEditarCarpetaId').value = id;
      document.getElementById('gdEditarCarpetaNombre').value = nombre || '';
      document.getElementById('gdEditarCarpetaDialog').showModal();
    }
  </script>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
