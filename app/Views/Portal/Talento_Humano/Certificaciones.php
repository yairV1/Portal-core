<?php
$titulo = 'Certificaciones laborales';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">

<a href="<?= BASE_URL ?>/talento-humano" class="doc-back">
  <i class="bi bi-arrow-left"></i> Volver a Talento Humano
</a>

<div class="doc-hero">
  <h1 class="doc-hero-titulo">Certificaciones laborales</h1>
  <p class="doc-hero-desc">Certificado laboral por empleado, con su fecha de expedición.</p>
</div>

<?php if ($puedeAdministrar): ?>
<div class="doc-section-head"><h4>Nuevo empleado</h4></div>
<form action="<?= BASE_URL ?>/talento-humano/empleados/crear" method="post" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:30px;align-items:flex-start">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
  <input type="text" name="nombre_completo" placeholder="Nombre completo" required class="doc-input" style="flex:1 1 200px">
  <input type="text" name="documento" placeholder="Documento de identidad" required class="doc-input" style="flex:1 1 160px">
  <input type="text" name="cargo" placeholder="Cargo" class="doc-input" style="flex:1 1 160px">
  <input type="date" name="fecha_ingreso" class="doc-input" style="flex:1 1 160px">
  <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-person-plus"></i> Agregar empleado</button>
</form>
<?php endif; ?>

<div class="doc-section-head"><h4>Empleados</h4></div>
<?php if (!$empleados): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-award"></i></div>
    <h4>Aún no hay empleados</h4>
    <p>Agrega el primero con el formulario de arriba.</p>
  </div>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Nombre</th><th>Documento</th><th>Cargo</th><th>Certificación laboral</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($empleados as $emp): ?>
        <tr>
          <td><strong><?= e($emp['nombre_completo']) ?></strong></td>
          <td style="opacity:.75"><?= e($emp['documento']) ?></td>
          <td style="opacity:.75"><?= e($emp['cargo'] ?: '—') ?></td>
          <td>
            <?php if ($emp['doc_id'] && $emp['doc_archivo']): ?>
              <span><?= e($emp['doc_nombre']) ?></span>
              <span class="text-muted" style="font-size:12px"> · <?= e((new DateTime($emp['fecha_expedicion']))->format('d/m/Y')) ?></span>
              <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= BASE_URL ?>/talento-humano/certificaciones-laborales/descargar?id=<?= (int) $emp['doc_id'] ?>" target="_blank" rel="noopener" title="Descargar"><i class="bi bi-download"></i></a>
              <?php if ($puedeAdministrar): ?>
                <form action="<?= BASE_URL ?>/talento-humano/certificaciones-laborales/eliminar" method="post" style="display:inline-block" onsubmit="return confirm('¿Eliminar esta certificación?')">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="doc_id" value="<?= (int) $emp['doc_id'] ?>">
                  <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                </form>
              <?php endif; ?>
            <?php elseif ($puedeAdministrar): ?>
              <details>
                <summary class="doc-btn doc-btn--secondary" style="display:inline-flex;cursor:pointer"><i class="bi bi-upload"></i> Subir</summary>
                <form action="<?= BASE_URL ?>/talento-humano/certificaciones-laborales/subir" method="post" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="empleado_id" value="<?= (int) $emp['id'] ?>">
                  <input type="text" name="nombre" placeholder="Nombre del documento" required class="doc-input" style="flex:1 1 160px">
                  <input type="date" name="fecha_expedicion" required title="Fecha de expedición" class="doc-input" style="flex:1 1 140px">
                  <input type="file" name="archivo" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                  <button type="submit" class="doc-btn doc-btn--primary">Subir</button>
                </form>
              </details>
            <?php else: ?>
              <span class="text-muted">— sin subir —</span>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap">
            <?php if ($puedeAdministrar): ?>
              <details>
                <summary class="doc-btn doc-btn--secondary" style="display:inline-flex;cursor:pointer"><i class="bi bi-pencil"></i> Editar</summary>
                <form action="<?= BASE_URL ?>/talento-humano/empleados/editar" method="post" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;padding:14px;border:1px solid var(--color-divider);border-radius:10px;background:var(--color-surface)">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
                  <input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                  <input type="text" name="nombre_completo" value="<?= e($emp['nombre_completo']) ?>" required class="doc-input" style="flex:1 1 200px">
                  <input type="text" name="documento" value="<?= e($emp['documento']) ?>" required class="doc-input" style="flex:1 1 160px">
                  <input type="text" name="cargo" value="<?= e($emp['cargo'] ?? '') ?>" placeholder="Cargo" class="doc-input" style="flex:1 1 160px">
                  <input type="text" name="telefono" value="<?= e($emp['telefono'] ?? '') ?>" placeholder="Teléfono" class="doc-input" style="flex:1 1 140px">
                  <input type="email" name="correo" value="<?= e($emp['correo'] ?? '') ?>" placeholder="Correo" class="doc-input" style="flex:1 1 180px">
                  <input type="date" name="fecha_ingreso" value="<?= e($emp['fecha_ingreso'] ?? '') ?>" class="doc-input" style="flex:1 1 160px">
                  <select name="estado" class="doc-input" style="flex:1 1 120px">
                    <option value="activo" <?= $emp['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $emp['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                  </select>
                  <button type="submit" class="doc-btn doc-btn--primary"><i class="bi bi-check-lg"></i> Guardar</button>
                </form>
              </details>
              <form action="<?= BASE_URL ?>/talento-humano/empleados/eliminar" method="post" style="display:inline-block" onsubmit="return confirm('¿Eliminar a <?= e(addslashes($emp['nombre_completo'])) ?>? También se borran su hoja de vida, contrato y certificaciones.')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
                <input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                <button type="submit" class="doc-btn doc-btn--danger doc-btn--icon" aria-label="Eliminar empleado"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
