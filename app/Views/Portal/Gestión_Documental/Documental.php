<?php $titulo = 'Gestión Documental'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/gestion-documental.css">
<h1 class="page-title">Gestión Documental</h1>
<p class="page-desc">Repositorio institucional de documentos, formatos y control de versiones.</p>

<?php $ESTADO_TAG = ['Vigente' => 'success', 'En revisión' => 'warning', 'Obsoleto' => 'danger']; ?>

<?php if (!$direccionesDoc): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-cone-striped"></i></div>
    <h4>Módulo en construcción</h4>
    <p>Este módulo todavía no tiene contenido cargado. Vuelve pronto.</p>
  </div>
<?php else: foreach ($direccionesDoc as $i => $dir): ?>
  <div class="<?= $i === 0 ? '' : 'subsection' ?>">
    <h4 class="section-title"><?= e($dir['label']) ?></h4>

    <?php foreach ($areasPorDireccion[$dir['id']] as $area): ?>
      <p class="text-muted" style="margin:18px 0 8px"><?= e($area['label']) ?></p>
      <table class="table" style="margin-bottom:24px">
        <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Responsable</th><th>Actualizado</th></tr></thead>
        <tbody>
          <?php foreach ($archivosPorCarpeta[$area['id']] as $arc): ?>
            <tr>
              <td><strong><?= e($arc['nombre']) ?></strong></td>
              <td style="opacity:.7"><?= e($arc['tipo']) ?></td>
              <td>
                <span style="display:inline-flex;align-items:center;gap:8px">
                  <span class="tag-stamp"><?= e($arc['version']) ?></span>
                  <span class="tag tag-<?= e($ESTADO_TAG[$arc['estado']] ?? 'info') ?>"><?= e($arc['estado']) ?></span>
                </span>
              </td>
              <td style="opacity:.7"><?= e($arc['responsable']) ?></td>
              <td style="opacity:.7"><?= $arc['fecha'] ? (new DateTime($arc['fecha']))->format('d/m/Y') : '' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  </div>
<?php endforeach; endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
