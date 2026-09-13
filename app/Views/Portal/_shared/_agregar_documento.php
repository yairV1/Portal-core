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
<button type="button" class="doc-btn doc-btn--primary doc-modal-trigger" onclick="document.getElementById('<?= e($idModal) ?>').showModal()">
  <i class="bi bi-plus-lg"></i> Agregar
</button>

<dialog id="<?= e($idModal) ?>" class="doc-modal">
  <form action="<?= BASE_URL ?>/documentos/crear" method="post">
    <div class="doc-modal-body">
      <h4>Agregar documento</h4>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="tipo" value="direccion">
      <input type="hidden" name="volver" value="<?= e($rutaModuloActual) ?>">
      <input type="hidden" name="area" value="<?= e($areaActiva) ?>">
      <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">

      <div class="doc-modal-tabs">
        <input type="radio" name="categoria" value="documento" id="<?= e($idModal) ?>-cat-doc" checked>
        <label for="<?= e($idModal) ?>-cat-doc">Documento</label>
        <input type="radio" name="categoria" value="formato" id="<?= e($idModal) ?>-cat-formato">
        <label for="<?= e($idModal) ?>-cat-formato">Formato (plantilla en blanco)</label>
      </div>

      <input type="text" name="nombre" placeholder="Nombre" required class="doc-input">
      <input type="text" name="tipo_doc" placeholder="Tipo (opcional)" class="doc-input">
      <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" class="doc-input">

      <div class="doc-modal-actions">
        <button type="button" class="doc-btn doc-btn--secondary" onclick="this.closest('dialog').close()">Cancelar</button>
        <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-plus-lg"></i> Agregar</button>
      </div>
    </div>
  </form>
</dialog>
