<?php $titulo = 'Postulaciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<?php ui_page_header([
    'title'   => 'Postulaciones',
    'desc'    => 'Candidatos que se postularon desde "Trabaja con nosotros". Solo visible para administradores.',
    'eyebrow' => 'Administración',
]); ?>

<?php if (!$postulaciones): ?>
  <?php ui_empty_state(['icon' => 'inbox', 'title' => 'Sin postulaciones todavía', 'text' => 'Cuando alguien se postule desde la landing pública, aparecerá acá.']); ?>
<?php else: ?>
  <div class="filter-bar">
    <div class="input-group search-field">
      <i class="bi bi-search input-icon" aria-hidden="true"></i>
      <input class="input" type="search" placeholder="Buscar por nombre, correo o cargo" aria-label="Buscar postulaciones" data-table-filter="tablaPostulaciones">
    </div>
    <span class="filter-bar-end text-muted"><?= count($postulaciones) ?> postulación<?= count($postulaciones) === 1 ? '' : 'es' ?></span>
  </div>
  <div class="table-wrap">
  <table class="table table--stack" id="tablaPostulaciones">
    <thead>
      <tr>
        <th>Candidato</th>
        <th>Contacto</th>
        <th>Cargo</th>
        <th>Vacante</th>
        <th>Fecha</th>
        <th>Archivos</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($postulaciones as $p): ?>
        <tr>
          <td class="cell-strong"><?= e($p['nombre']) ?></td>
          <td class="cell-muted">
            <?= e($p['correo']) ?><br><?= e($p['telefono']) ?>
          </td>
          <td><?= e($p['cargo_aplicado']) ?></td>
          <td class="cell-muted"><?= $p['vacante_titulo'] ? e($p['vacante_titulo']) : '—' ?></td>
          <td class="cell-muted"><?= (new DateTime($p['creado_en']))->format('d/m/Y H:i') ?></td>
          <td>
            <div class="btn-group">
              <?php if ($p['hoja_vida_archivo']): ?>
                <a class="btn btn-sm" href="<?= BASE_URL ?>/postulaciones/descargar?tipo=cv&id=<?= (int) $p['id'] ?>">
                  <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> CV
                </a>
              <?php endif; ?>
              <?php if ($p['foto_archivo']): ?>
                <a class="btn btn-sm" href="<?= BASE_URL ?>/postulaciones/descargar?tipo=foto&id=<?= (int) $p['id'] ?>">
                  <i class="bi bi-image" aria-hidden="true"></i> Foto
                </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="empty-state empty-state--compact empty-state--bare" data-filter-empty="tablaPostulaciones" hidden>
    <div class="ic" aria-hidden="true"><i class="bi bi-search" aria-hidden="true"></i></div><p>Ninguna postulación coincide con la búsqueda.</p>
  </div>
  </div>
<?php endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
