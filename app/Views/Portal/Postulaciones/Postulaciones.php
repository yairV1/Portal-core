<?php $titulo = 'Postulaciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<h1 class="page-title">Postulaciones</h1>
<p class="page-desc">Candidatos que se postularon desde "Trabaja con nosotros" — solo visible para administradores.</p>

<?php if (!$postulaciones): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-inbox"></i></div>
    <h4>Sin postulaciones todavía</h4>
    <p>Cuando alguien se postule desde la landing pública, aparecerá acá.</p>
  </div>
<?php else: ?>
  <table class="table">
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
          <td><strong><?= e($p['nombre']) ?></strong></td>
          <td style="opacity:.8">
            <?= e($p['correo']) ?><br>
            <span style="opacity:.7"><?= e($p['telefono']) ?></span>
          </td>
          <td><?= e($p['cargo_aplicado']) ?></td>
          <td style="opacity:.7"><?= $p['vacante_titulo'] ? e($p['vacante_titulo']) : '—' ?></td>
          <td style="opacity:.7"><?= (new DateTime($p['creado_en']))->format('d/m/Y H:i') ?></td>
          <td>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
              <?php if ($p['hoja_vida_archivo']): ?>
                <a class="tag tag-accent" href="<?= BASE_URL ?>/postulaciones/descargar?tipo=cv&id=<?= (int) $p['id'] ?>">
                  <i class="bi bi-file-earmark-pdf"></i> CV
                </a>
              <?php endif; ?>
              <?php if ($p['foto_archivo']): ?>
                <a class="tag tag-neutral" href="<?= BASE_URL ?>/postulaciones/descargar?tipo=foto&id=<?= (int) $p['id'] ?>">
                  <i class="bi bi-image"></i> Foto
                </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
