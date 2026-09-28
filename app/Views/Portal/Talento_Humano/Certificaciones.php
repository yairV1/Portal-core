<?php
$titulo = 'Certificaciones laborales';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
// Configuración para el listado compartido (ver _shared/_empleados_documento.php).
$ED = [
    'titulo'    => 'Certificaciones laborales',
    'desc'      => 'Certificado laboral por empleado, con su fecha de expedición.',
    'icono'     => 'award',
    'columna'   => 'Certificación laboral',
    'segmento'  => 'certificaciones-laborales',
    'singular'  => 'certificación laboral',
    'confirmar' => '¿Eliminar esta certificación?',
    'resumen'   => function (array $emp) { ?>
        <span class="emp-doc-nombre"><?= e($emp['doc_nombre']) ?></span>
        <span class="emp-doc-meta">Expedida el <?= e((new DateTime($emp['fecha_expedicion']))->format('d/m/Y')) ?></span>
    <?php },
    'campos'    => function () { ?>
        <div class="field">
          <label class="field-label" for="dsFecha">Fecha de expedición <span class="req" aria-hidden="true">*</span></label>
          <input class="input" type="date" id="dsFecha" name="fecha_expedicion" required>
        </div>
    <?php },
];
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/empleados.css') ?>">
<?php require ROOT_PATH . '/app/Views/Portal/_shared/_empleados_documento.php'; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
