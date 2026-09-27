<?php
/**
 * Explorador de carpetas — compartido entre Administrativa y Financiera y
 * Talento Humano (antes vivía solo/duplicado en Administrativa_Financiera/
 * _explorador.php). Se incluye así:
 *   <?php $rutaModuloActual = '/talento-humano'; require ROOT_PATH . '/app/Views/Portal/_shared/_explorador_documental.php'; ?>
 *
 * Variables que ya vienen listas desde PortalController.php: $direccion,
 * $carpetaActual, $rutaCarpetas, $subcarpetas, $archivosCarpeta — más
 * $csrf/BASE_URL/e() que vienen desde public/index.php. $rutaModuloActual
 * lo define la vista que incluye este parcial (es lo único que cambia
 * entre módulos). El aviso de resultado (?drive=...) lo muestra
 * portal-footer.php como toast, mismo patrón que ?doc=/?evento=/?pendiente=
 * — no hay banner propio acá.
 */
$esAdminExplorador = usuario_admin_de($direccion['id'] ?? null);
$urlVolverExplorador = BASE_URL . $rutaModuloActual . ($carpetaActual ? '?carpeta=' . (int) $carpetaActual['id'] : '');
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/explorador.css') ?>">

<section class="explorador" id="explorador">
  <div class="section-header"><h2 class="section-heading"><?= e($carpetaActual['nombre']) ?></h2></div>

  <?php if (count($rutaCarpetas) > 1): // solo suma valor cuando de verdad hay subcarpetas de por medio. ?>
    <nav class="explorador-breadcrumb" aria-label="Ruta de carpetas">
      <?php foreach ($rutaCarpetas as $i => $c): ?>
        <?php if ($i > 0): ?><span> / </span><?php endif; ?>
        <a href="<?= BASE_URL . $rutaModuloActual ?>?carpeta=<?= (int) $c['id'] ?>"><?= e($c['nombre']) ?></a>
      <?php endforeach; ?>
    </nav>
  <?php endif; ?>

  <?php if ($esAdminExplorador):
    // Antes eran 3 formularios siempre abiertos en la barra de herramientas
    // (Nueva carpeta/Subir documento/Traer de Drive, compitiendo por
    // atención) — ahora es un solo botón que abre un modal con esas 3
    // acciones como pestañas (componente .modal, core/components.css).
    $idModalExp = 'modalExp' . abs(crc32($rutaModuloActual . '-' . $carpetaActual['id']));
  ?>
  <div class="explorador-toolbar">
    <button type="button" class="btn btn-primary" data-open="<?= e($idModalExp) ?>" aria-haspopup="dialog">
      <i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar
    </button>
  </div>

  <dialog id="<?= e($idModalExp) ?>" class="modal" aria-labelledby="<?= e($idModalExp) ?>-t">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="<?= e($idModalExp) ?>-t">Agregar a «<?= e($carpetaActual['nombre']) ?>»</h2>
        <p class="modal-desc">Crea una subcarpeta, sube un archivo desde tu equipo o tráelo de Google Drive.</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>

    <div class="modal-toolbar">
      <div class="segmented segmented--block" data-tabs aria-label="Qué quieres agregar">
        <button type="button" class="segmented-item is-active" data-tab="carpeta"><i class="bi bi-folder-plus" aria-hidden="true"></i> Carpeta</button>
        <button type="button" class="segmented-item" data-tab="subir"><i class="bi bi-upload" aria-hidden="true"></i> Archivo</button>
        <button type="button" class="segmented-item" data-tab="drive"><i class="bi bi-google" aria-hidden="true"></i> Drive</button>
        <?php if (onlyoffice_configurado()): ?>
          <button type="button" class="segmented-item" data-tab="nuevo"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Nuevo</button>
        <?php endif; ?>
      </div>
    </div>

    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/crear" method="post" data-tab-panel="carpeta">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <div class="modal-body form-stack">
        <div class="field">
          <label class="field-label" for="<?= e($idModalExp) ?>-carpeta">Nombre de la carpeta <span class="req" aria-hidden="true">*</span></label>
          <input class="input" type="text" id="<?= e($idModalExp) ?>-carpeta" name="nombre" maxlength="150" required placeholder="Ej. Actas 2026">
        </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-folder-plus" aria-hidden="true"></i> Crear carpeta</button>
      </footer>
    </form>

    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/subir" method="post" enctype="multipart/form-data" data-tab-panel="subir" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <div class="modal-body form-stack">
        <div class="field">
          <span class="field-label" id="<?= e($idModalExp) ?>-archivo-l">Archivo <span class="req" aria-hidden="true">*</span></span>
          <label class="file-drop">
            <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
            <span><strong>Elige un archivo</strong> o arrástralo aquí</span>
            <span class="file-drop-name"></span>
            <input type="file" name="documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp" required aria-labelledby="<?= e($idModalExp) ?>-archivo-l">
          </label>
          <p class="field-hint">PDF, Word, Excel, PowerPoint o imagen (JPG, PNG, WEBP) · máximo 15 MB</p>
        </div>
        <div class="field">
          <label class="field-label" for="<?= e($idModalExp) ?>-tipo">Tipo <span class="opt">(opcional)</span></label>
          <input class="input" type="text" id="<?= e($idModalExp) ?>-tipo" name="tipo" placeholder="Ej. Resolución, Acta, Informe">
        </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-upload" aria-hidden="true"></i> Subir archivo</button>
      </footer>
    </form>

    <!-- Traer un archivo de Google Drive en vez de subirlo desde el equipo
         (ver GoogleDrive.php/.env.example — debe estar compartido con la
         cuenta de servicio). -->
    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/importar-drive" method="post" data-tab-panel="drive" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <div class="modal-body form-stack">
        <div class="field">
          <label class="field-label" for="<?= e($idModalExp) ?>-drive">Link del archivo en Google Drive</label>
          <div class="input-group">
            <i class="bi bi-link-45deg input-icon" aria-hidden="true"></i>
            <input class="input" type="text" id="<?= e($idModalExp) ?>-drive" name="drive_link" placeholder="https://drive.google.com/file/d/…" inputmode="url">
          </div>
          <p class="field-hint">El archivo debe estar compartido con la cuenta de servicio del portal.</p>
        </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-google" aria-hidden="true"></i> Traer de Drive</button>
      </footer>
    </form>

    <?php if (onlyoffice_configurado()): ?>
    <!-- Documento en blanco (Word/Excel/PowerPoint) para escribir dentro
         del portal — ver CarpetaController.php /crear-documento y
         EditorController.php. Al enviar, manda derecho al editor. -->
    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/crear-documento" method="post" data-tab-panel="nuevo" hidden>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <div class="modal-body form-stack">
        <div class="field">
          <label class="field-label" for="<?= e($idModalExp) ?>-nuevo">Nombre del documento <span class="opt">(opcional)</span></label>
          <input class="input" type="text" id="<?= e($idModalExp) ?>-nuevo" name="nombre" maxlength="150" placeholder="Documento sin título">
          <p class="field-hint">Elige el tipo abajo: se crea en blanco y se abre en el editor del portal.</p>
        </div>
      </div>
      <footer class="modal-footer">
        <button type="button" class="btn" data-close>Cancelar</button>
        <button type="submit" name="formato" value="docx" class="btn"><i class="bi bi-file-earmark-word" aria-hidden="true"></i> Word</button>
        <button type="submit" name="formato" value="xlsx" class="btn"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Excel</button>
        <button type="submit" name="formato" value="pptx" class="btn"><i class="bi bi-file-earmark-ppt" aria-hidden="true"></i> PowerPoint</button>
      </footer>
    </form>
    <?php endif; ?>
  </dialog>
  <?php endif; ?>

  <?php if (!$subcarpetas && !$archivosCarpeta): ?>
    <div class="empty-state">
      <div class="ic"><i class="bi bi-folder2-open"></i></div>
      <h4>Esta carpeta está vacía</h4>
      <p>
        <?php if (!$esAdminExplorador): ?>
          Todavía no hay documentos acá.
        <?php elseif ($carpetaActual): ?>
          Sube un documento o crea una subcarpeta.
        <?php else: ?>
          Crea la primera carpeta para empezar a guardar documentos.
        <?php endif; ?>
      </p>
    </div>
  <?php else: ?>
    <div class="explorador-grid">
      <?php foreach ($subcarpetas as $c): ?>
        <a href="<?= BASE_URL . $rutaModuloActual ?>?carpeta=<?= (int) $c['id'] ?>" class="explorador-item explorador-item-carpeta">
          <i class="bi bi-folder-fill"></i>
          <span><?= e($c['nombre']) ?></span>
        </a>
      <?php endforeach; ?>

      <?php foreach ($archivosCarpeta as $a): ?>
        <div class="explorador-item explorador-item-archivo">
          <a href="<?= BASE_URL . $rutaModuloActual ?>/carpetas/descargar?id=<?= (int) $a['id'] ?>" class="explorador-item-link" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span><?= e($a['nombre']) ?></span>
            <?php if (!empty($a['tipo'])): ?><span class="doc-tag-origen"><?= e($a['tipo']) ?></span><?php endif; ?>
          </a>
          <?php if (onlyoffice_configurado() && onlyoffice_editable($a['archivo'])): ?>
            <a href="<?= BASE_URL ?>/editor?tipo=carpeta&id=<?= (int) $a['id'] ?>&volver=<?= urlencode($urlVolverExplorador) ?>"
               class="btn btn-icon explorador-item-editar" aria-label="<?= $esAdminExplorador ? 'Editar' : 'Abrir' ?> dentro del portal" title="<?= $esAdminExplorador ? 'Editar' : 'Abrir' ?> dentro del portal">
              <i class="bi <?= $esAdminExplorador ? 'bi-pencil-square' : 'bi-eye' ?>"></i>
            </a>
          <?php endif; ?>
          <?php if ($esAdminExplorador): ?>
            <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/eliminar" method="post" data-confirm="¿Eliminar este documento?" data-confirm-ok="Eliminar">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
              <input type="hidden" name="archivo_id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="btn btn-danger-soft btn-icon explorador-item-borrar" aria-label="Eliminar documento"><i class="bi bi-trash"></i></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($esAdminExplorador && $carpetaActual): ?>
    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/eliminar" method="post"
 data-confirm="¿Eliminar esta carpeta?" data-confirm-text="También se eliminará todo lo que tenga dentro." data-confirm-ok="Eliminar" class="explorador-delete-folder">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <button type="submit" class="btn btn-danger-soft">
        <i class="bi bi-trash"></i> Eliminar esta carpeta
      </button>
    </form>
  <?php endif; ?>
</section>
