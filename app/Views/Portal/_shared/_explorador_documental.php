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
  <div class="section-head"><h4><?= e($carpetaActual['nombre']) ?></h4></div>

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
    // acciones como pestañas (ver .doc-modal en centro-documental.css).
    $idModalExp = 'modalExp' . abs(crc32($rutaModuloActual . '-' . $carpetaActual['id']));
  ?>
  <div class="explorador-toolbar">
    <button type="button" class="doc-btn doc-btn--primary doc-modal-trigger" onclick="document.getElementById('<?= e($idModalExp) ?>').showModal()">
      <i class="bi bi-plus-lg"></i> Agregar
    </button>
  </div>

  <dialog id="<?= e($idModalExp) ?>" class="doc-modal">
    <div class="doc-modal-body">
      <h4>Agregar a esta carpeta</h4>

      <div class="doc-modal-tabs" data-modal-tabs>
        <button type="button" class="doc-modal-tab-btn active" data-tab="carpeta"><i class="bi bi-folder-plus"></i> Carpeta</button>
        <button type="button" class="doc-modal-tab-btn" data-tab="subir"><i class="bi bi-upload"></i> Archivo</button>
        <button type="button" class="doc-modal-tab-btn" data-tab="drive"><i class="bi bi-google"></i> Drive</button>
        <?php if (onlyoffice_configurado()): ?>
          <button type="button" class="doc-modal-tab-btn" data-tab="nuevo"><i class="bi bi-file-earmark-plus"></i> Documento nuevo</button>
        <?php endif; ?>
      </div>

      <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/crear" method="post" class="doc-modal-section" data-section="carpeta">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
        <input type="text" name="nombre" placeholder="Nombre de la nueva carpeta" maxlength="150" required class="doc-input">
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-folder-plus"></i> Crear carpeta</button>
        </div>
      </form>

      <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/subir" method="post" enctype="multipart/form-data" class="doc-modal-section" data-section="subir" hidden>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
        <input type="text" name="tipo" placeholder="Tipo (opcional)" class="doc-input">
        <input type="file" name="documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp" required class="doc-input">
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-upload"></i> Subir</button>
        </div>
      </form>

      <!-- Segunda forma de agregar un documento: traerlo de Google Drive en
           vez de subirlo desde el equipo (ver GoogleDrive.php/.env.example
           — el archivo debe estar compartido con la cuenta de servicio). -->
      <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/importar-drive" method="post" class="doc-modal-section" data-section="drive" hidden>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
        <input type="text" name="drive_link" placeholder="Link del archivo de Google Drive" class="doc-input">
        <div class="doc-modal-actions">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" class="doc-btn doc-btn--secondary"><i class="bi bi-google"></i> Traer de Drive</button>
        </div>
      </form>

      <?php if (onlyoffice_configurado()): ?>
      <!-- Tercera forma de agregar un documento: uno en blanco (Word/Excel/
           PowerPoint) para escribir de una vez dentro del portal, sin tener
           ya un archivo hecho — ver CarpetaController.php /crear-documento
           y EditorController.php. Al enviar, manda derecho al editor. -->
      <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/crear-documento" method="post" class="doc-modal-section" data-section="nuevo" hidden>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
        <input type="text" name="nombre" placeholder="Nombre del documento (opcional)" maxlength="150" class="doc-input">
        <div class="doc-modal-actions doc-modal-actions--nuevo">
          <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
          <button type="submit" name="formato" value="docx" class="doc-btn doc-btn--secondary"><i class="bi bi-file-earmark-word"></i> Word</button>
          <button type="submit" name="formato" value="xlsx" class="doc-btn doc-btn--secondary"><i class="bi bi-file-earmark-excel"></i> Excel</button>
          <button type="submit" name="formato" value="pptx" class="doc-btn doc-btn--secondary"><i class="bi bi-file-earmark-ppt"></i> PowerPoint</button>
        </div>
      </form>
      <?php endif; ?>
    </div>
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
               class="doc-btn doc-btn--secondary doc-btn--icon explorador-item-editar" aria-label="<?= $esAdminExplorador ? 'Editar' : 'Abrir' ?> dentro del portal" title="<?= $esAdminExplorador ? 'Editar' : 'Abrir' ?> dentro del portal">
              <i class="bi <?= $esAdminExplorador ? 'bi-pencil-square' : 'bi-eye' ?>"></i>
            </a>
          <?php endif; ?>
          <?php if ($esAdminExplorador): ?>
            <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar este documento?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
              <input type="hidden" name="archivo_id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon explorador-item-borrar" aria-label="Eliminar documento"><i class="bi bi-trash"></i></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($esAdminExplorador && $carpetaActual): ?>
    <form action="<?= BASE_URL . $rutaModuloActual ?>/carpetas/eliminar" method="post"
          onsubmit="return confirm('¿Eliminar esta carpeta y TODO lo que tenga dentro?')" class="explorador-delete-folder">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <button type="submit" class="doc-btn doc-btn--danger">
        <i class="bi bi-trash"></i> Eliminar esta carpeta
      </button>
    </form>
  <?php endif; ?>
</section>
