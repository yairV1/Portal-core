<?php
$titulo = 'Hojas de vida';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración para el listado compartido (ver _shared/_empleados_documento.php).
$ED = [
    'titulo'    => 'Hojas de vida',
    'desc'      => 'Hoja de vida por empleado, con su archivo adjunto.',
    'icono'     => 'person-vcard',
    'columna'   => 'Hoja de vida',
    'segmento'  => 'hojas-de-vida',
    'singular'  => 'hoja de vida',
    'confirmar' => '¿Eliminar esta hoja de vida?',
    'resumen'   => function (array $emp) { ?>
        <span class="emp-doc-nombre"><?= e($emp['doc_nombre']) ?></span>
        <span class="emp-doc-meta"><?= e((new DateTime($emp['fecha']))->format('d/m/Y')) ?></span>
    <?php },
    'campos'    => function () { ?>
        <div class="field">
          <label class="field-label" for="dsFecha">Fecha <span class="req" aria-hidden="true">*</span></label>
          <input class="input" type="date" id="dsFecha" name="fecha" required>
        </div>
    <?php },
];
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/empleados.css') ?>">
<?php require ROOT_PATH . '/app/Views/Portal/_shared/_empleados_documento.php'; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
