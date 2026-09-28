<?php
$titulo = 'Contratos';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
$TIPO_CONTRATO_LABEL = [
    'termino_fijo'          => 'Término fijo',
    'indefinido'            => 'Indefinido',
    'prestacion_servicios'  => 'Prestación de servicios',
];
// Configuración para el listado compartido (ver _shared/_empleados_documento.php).
$ED = [
    'titulo'    => 'Contratos',
    'desc'      => 'Contrato vigente por empleado, con tipo y vigencia.',
    'icono'     => 'file-earmark-ruled',
    'columna'   => 'Contrato',
    'segmento'  => 'contratos',
    'singular'  => 'contrato',
    'confirmar' => '¿Eliminar este contrato?',
    'resumen'   => function (array $emp) use ($TIPO_CONTRATO_LABEL) { ?>
        <span class="emp-doc-nombre"><?= e($emp['doc_nombre']) ?> <span class="badge badge-neutral"><?= e($TIPO_CONTRATO_LABEL[$emp['tipo_contrato']] ?? $emp['tipo_contrato']) ?></span></span>
        <span class="emp-doc-meta"><?= e((new DateTime($emp['fecha_inicio']))->format('d/m/Y')) ?> — <?= $emp['fecha_fin'] ? e((new DateTime($emp['fecha_fin']))->format('d/m/Y')) : 'indefinido' ?></span>
    <?php },
    'campos'    => function () use ($TIPO_CONTRATO_LABEL) { ?>
        <div class="field">
          <label class="field-label" for="dsTipo">Tipo de contrato <span class="req" aria-hidden="true">*</span></label>
          <select class="select" id="dsTipo" name="tipo_contrato" required>
            <option value="">Selecciona…</option>
            <?php foreach ($TIPO_CONTRATO_LABEL as $valor => $label): ?>
              <option value="<?= e($valor) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grid">
          <div class="field">
            <label class="field-label" for="dsInicio">Fecha de inicio <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="date" id="dsInicio" name="fecha_inicio" required>
          </div>
          <div class="field">
            <label class="field-label" for="dsFin">Fecha de fin <span class="opt">(opcional)</span></label>
            <input class="input" type="date" id="dsFin" name="fecha_fin">
            <p class="field-hint">Vacía = indefinido.</p>
          </div>
        </div>
    <?php },
];
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/empleados.css') ?>">
<?php require ROOT_PATH . '/app/Views/Portal/_shared/_empleados_documento.php'; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
