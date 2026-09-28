<?php
/**
 * Botón "+ Agregar" + modal — reemplaza, en el panel principal de los 6
 * módulos de "centro documental", tres cosas que antes competían por
 * atención: el botón "Subir archivo" del hero (que solo hacía scroll hacia
 * abajo), y la grilla siempre-abierta "Agregar un archivo suelto" (dos
 * formularios lado a lado, Documento/Formato). Ahora es un solo botón que
 * abre un <dialog> con esas mismas dos opciones como pestañas.
 *
 * Se incluye así (mismo criterio que _explorador_documental.php):
 *   <?php $rutaModuloActual = '/talento-humano'; require ROOT_PATH . '/app/Views/Portal/_shared/_agregar_documento.php'; ?>
 *
 * Variables que ya vienen listas desde PortalController.php: $direccion,
 * $areaActiva — más $csrf/BASE_URL/e() desde public/index.php.
 * $rutaModuloActual lo define la vista que incluye este parcial.
 * Solo se pinta algo si $esAdminDoc es true (la vista ya lo calcula antes
 * de incluir este parcial).
 */
if (!$esAdminDoc) return;
$idModal = 'modalAgregar' . abs(crc32($rutaModuloActual));
?>
<button type="button" class="btn btn-primary" data-open="<?= e($idModal) ?>" aria-haspopup="dialog">
  <i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar
</button>

<dialog id="<?= e($idModal) ?>" class="modal" aria-labelledby="<?= e($idModal) ?>-t">
  <form action="<?= BASE_URL ?>/documentos/crear" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="tipo" value="direccion">
    <input type="hidden" name="volver" value="<?= e($rutaModuloActual) ?>">
    <input type="hidden" name="area" value="<?= e($areaActiva) ?>">
    <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">

    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="<?= e($idModal) ?>-t">Agregar documento</h2>
        <p class="modal-desc">Regístralo primero; el archivo se adjunta después desde "Pendientes de adjuntar".</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>

    <div class="modal-body form-stack">
      <div class="field">
        <span class="field-label" id="<?= e($idModal) ?>-cat-l">Categoría</span>
        <div class="segmented segmented--block" role="radiogroup" aria-labelledby="<?= e($idModal) ?>-cat-l">
          <input type="radio" name="categoria" value="documento" id="<?= e($idModal) ?>-cat-doc" checked>
          <label for="<?= e($idModal) ?>-cat-doc"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Documento</label>
          <input type="radio" name="categoria" value="formato" id="<?= e($idModal) ?>-cat-formato">
          <label for="<?= e($idModal) ?>-cat-formato"><i class="bi bi-file-earmark" aria-hidden="true"></i> Formato</label>
        </div>
        <p class="field-hint">Un formato es una plantilla en blanco para que otros la diligencien.</p>
      </div>
      <div class="field">
        <label class="field-label" for="<?= e($idModal) ?>-nombre">Nombre <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="<?= e($idModal) ?>-nombre" name="nombre" required placeholder="Ej. Manual de procesos financieros">
      </div>
      <div class="form-grid">
        <div class="field">
          <label class="field-label" for="<?= e($idModal) ?>-tipo">Tipo <span class="opt">(opcional)</span></label>
          <input class="input" type="text" id="<?= e($idModal) ?>-tipo" name="tipo_doc" placeholder="Ej. Manual">
        </div>
        <div class="field">
          <label class="field-label" for="<?= e($idModal) ?>-fecha">Fecha</label>
          <input class="input" type="date" id="<?= e($idModal) ?>-fecha" name="fecha" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
    </div>

    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
    </footer>
  </form>
</dialog>
