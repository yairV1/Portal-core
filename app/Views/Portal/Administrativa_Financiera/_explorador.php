<?php
/**
 * Explorador de documentos tipo "drive" — de momento solo se incluye desde
 * Views/Portal/Administrativa_Financiera/Financiera.php, con:
 *   <?php require __DIR__ . '/_explorador.php'; ?>
 * en el punto de la página donde debe aparecer.
 *
 * Variables que ya vienen listas desde PortalController.php: $direccion,
 * $carpetaActual, $rutaCarpetas, $subcarpetas, $archivosCarpeta — más
 * $csrf/BASE_URL/e() que vienen desde public/index.php. El aviso de
 * resultado (?drive=...) lo muestra portal-footer.php como toast, mismo
 * patrón que ?doc=/?evento=/?pendiente= — no hay banner propio acá.
 */
$esAdminExplorador = ($_SESSION['usuario_rol'] ?? '') === 'admin';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/explorador.css') ?>">

<section class="explorador" id="explorador">
  <div class="section-head"><h4>Documentos</h4></div>

  <nav class="explorador-breadcrumb" aria-label="Ruta de carpetas">
    <a href="<?= BASE_URL ?>/administrativa-financiera">Documentos</a>
    <?php foreach ($rutaCarpetas as $c): ?>
      <span> / </span>
      <a href="<?= BASE_URL ?>/administrativa-financiera?carpeta=<?= (int) $c['id'] ?>"><?= e($c['nombre']) ?></a>
    <?php endforeach; ?>
  </nav>

  <?php if ($esAdminExplorador): ?>
  <div class="explorador-toolbar">
    <form action="<?= BASE_URL ?>/administrativa-financiera/carpetas/crear" method="post" class="explorador-form-inline">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <?php if ($carpetaActual): ?><input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>"><?php endif; ?>
      <input type="text" name="nombre" placeholder="Nombre de la nueva carpeta" maxlength="150" required>
      <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer">
        <i class="bi bi-folder-plus"></i> Nueva carpeta
      </button>
    </form>

    <?php if ($carpetaActual): ?>
      <form action="<?= BASE_URL ?>/administrativa-financiera/carpetas/subir" method="post" enctype="multipart/form-data" class="explorador-form-inline">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
        <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
        <label class="explorador-file-btn tag tag-accent">
          <i class="bi bi-upload"></i> Subir documento
          <input type="file" name="documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
        </label>
      </form>
    <?php endif; ?>
  </div>
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
        <a href="<?= BASE_URL ?>/administrativa-financiera?carpeta=<?= (int) $c['id'] ?>" class="explorador-item explorador-item-carpeta">
          <i class="bi bi-folder-fill"></i>
          <span><?= e($c['nombre']) ?></span>
        </a>
      <?php endforeach; ?>

      <?php foreach ($archivosCarpeta as $a): ?>
        <div class="explorador-item explorador-item-archivo">
          <a href="<?= BASE_URL ?>/administrativa-financiera/carpetas/descargar?id=<?= (int) $a['id'] ?>" class="explorador-item-link">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span><?= e($a['nombre']) ?></span>
          </a>
          <?php if ($esAdminExplorador): ?>
            <form action="<?= BASE_URL ?>/administrativa-financiera/carpetas/eliminar" method="post" onsubmit="return confirm('¿Eliminar este documento?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
              <input type="hidden" name="archivo_id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="explorador-item-borrar" aria-label="Eliminar documento"><i class="bi bi-trash"></i></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($esAdminExplorador && $carpetaActual): ?>
    <form action="<?= BASE_URL ?>/administrativa-financiera/carpetas/eliminar" method="post"
          onsubmit="return confirm('¿Eliminar esta carpeta y TODO lo que tenga dentro?')" style="margin-top:18px">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
      <input type="hidden" name="carpeta_id" value="<?= (int) $carpetaActual['id'] ?>">
      <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer">
        <i class="bi bi-trash"></i> Eliminar esta carpeta
      </button>
    </form>
  <?php endif; ?>
</section>
