<?php $titulo = 'Contrataciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/contrataciones.css') ?>">
<?php
/** Variables desde ContratacionController.php:
 * @var ?string $nuevoToken
 * @var string  $origenAbsoluto
 * @var array   $postulacionesParaVincular
 * @var array   $contrataciones
 * @var array   $documentosPorContratacion
 */
$urlContratacion = fn (string $token) => $origenAbsoluto . BASE_URL . '/contratacion?token=' . $token;
?>

<?php ui_page_header([
    'title'   => 'Contrataciones',
    'desc'    => 'Genera enlaces privados para el formulario de contratación y revisa la documentación que envía cada candidato.',
    'eyebrow' => 'Administración',
    'actions' => function () { ?>
        <button type="button" class="btn btn-primary" data-open="enlaceNuevo" aria-haspopup="dialog"><i class="bi bi-link-45deg" aria-hidden="true"></i> Generar enlace</button>
    <?php },
]); ?>

<?php if ($nuevoToken): $enlace = $urlContratacion($nuevoToken); ?>
  <div class="alert alert-success" role="status">
    <i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>
    <div class="alert-content">
      <p class="alert-title">Enlace generado</p>
      <p class="alert-text">Cópialo y envíaselo al candidato. Solo sirve una vez.</p>
      <div class="contrat-enlace">
        <input class="input input-sm" type="text" readonly value="<?= e($enlace) ?>" aria-label="Enlace generado" onfocus="this.select()">
        <button type="button" class="btn btn-sm btn-primary" data-copy="<?= e($enlace) ?>" data-copy-label="Enlace copiado"><i class="bi bi-clipboard" aria-hidden="true"></i> Copiar</button>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (!$contrataciones): ?>
  <?php ui_empty_state([
      'icon' => 'link-45deg', 'title' => 'Todavía no hay enlaces',
      'text' => 'Genera un enlace y envíaselo al candidato para que complete su información y documentos.',
      'actions' => function () { ?><button type="button" class="btn btn-primary" data-open="enlaceNuevo"><i class="bi bi-link-45deg" aria-hidden="true"></i> Generar enlace</button><?php },
  ]); ?>
<?php else: ?>
  <div class="table-wrap">
    <table class="table table--stack">
      <thead><tr><th>Candidato</th><th>Estado</th><th>Detalle</th><th>Documentos</th><th class="col-actions"><span class="sr-only">Acciones</span></th></tr></thead>
      <tbody>
        <?php foreach ($contrataciones as $c): ?>
          <?php if (!$c['usado']): $expirado = strtotime($c['expira_en']) < time(); ?>
            <tr>
              <td>
                <span class="cell-strong"><?= e($c['nombre_referencia'] ?: 'Sin referencia') ?></span>
                <?php if ($c['postulacion_nombre']): ?><span class="contrat-sub">Vinculado a <?= e($c['postulacion_nombre']) ?></span><?php endif; ?>
              </td>
              <td><span class="badge badge-dot <?= $expirado ? 'badge-danger' : 'badge-warning' ?>"><?= $expirado ? 'Expirado' : 'Pendiente' ?></span></td>
              <td class="cell-muted"><?= $expirado ? 'Venció' : 'Vence' ?> el <?= (new DateTime($c['expira_en']))->format('d/m/Y') ?></td>
              <td>
                <?php if (!$expirado): ?>
                  <button type="button" class="btn btn-sm" data-copy="<?= e($urlContratacion($c['token'])) ?>" data-copy-label="Enlace copiado"><i class="bi bi-clipboard" aria-hidden="true"></i> Copiar enlace</button>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td class="col-actions">
                <form action="<?= BASE_URL ?>/contrataciones/eliminar" method="post" data-confirm="¿Eliminar este enlace sin usar?" data-confirm-text="<?= e($c['nombre_referencia'] ?: 'Sin referencia') ?>" data-confirm-ok="Eliminar">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" aria-label="Eliminar enlace"><i class="bi bi-trash" aria-hidden="true"></i></button>
                </form>
              </td>
            </tr>
          <?php else: ?>
            <tr>
              <td><span class="cell-strong"><?= e($c['nombre']) ?></span><span class="contrat-sub"><?= e($c['email']) ?></span></td>
              <td><span class="badge badge-success badge-dot">Completado</span></td>
              <td class="cell-muted">C.C. <?= e($c['cedula']) ?> · <?= e($c['celular']) ?><br><?= (new DateTime($c['completado_en']))->format('d/m/Y H:i') ?></td>
              <td>
                <div class="btn-group">
                  <?php foreach ($documentosPorContratacion[$c['id']] ?? [] as $doc): ?>
                    <a class="btn btn-sm" href="<?= BASE_URL ?>/contrataciones/descargar?id=<?= (int) $doc['id'] ?>"><i class="bi bi-download" aria-hidden="true"></i> <?= e($doc['tipo']) ?></a>
                  <?php endforeach; ?>
                </div>
              </td>
              <td class="col-actions"></td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- ── Generar enlace ── -->
<dialog class="modal" id="enlaceNuevo" aria-labelledby="enlaceNuevoT">
  <form action="<?= BASE_URL ?>/contrataciones/generar" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="enlaceNuevoT">Generar enlace de contratación</h2>
        <p class="modal-desc">El candidato completa sus datos y documentos desde ese enlace, sin necesidad de cuenta.</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="enReferencia">Nombre del candidato <span class="opt">(referencia interna)</span></label>
        <input class="input" type="text" id="enReferencia" name="nombre_referencia" placeholder="Ej. Laura Castaño">
      </div>
      <div class="field">
        <label class="field-label" for="enPostulacion">Postulación</label>
        <select class="select" id="enPostulacion" name="postulacion_id">
          <option value="">Sin vincular a una postulación</option>
          <?php foreach ($postulacionesParaVincular as $p): ?>
            <option value="<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="field-label" for="enVigencia">Vigencia en días</label>
        <input class="input" type="number" id="enVigencia" name="dias_vigencia" value="15" min="15" inputmode="numeric">
        <p class="field-hint">Mínimo 15 días.</p>
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-link-45deg" aria-hidden="true"></i> Generar enlace</button>
    </footer>
  </form>
</dialog>


<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
