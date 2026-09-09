<?php $titulo = 'Contrataciones'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<h1 class="page-title">Contrataciones</h1>
<p class="page-desc">Genera enlaces privados para el formulario de contratación y revisa lo que llegue — solo administradores.</p>

<?php if ($nuevoToken): ?>
  <div style="display:flex; gap:14px; align-items:flex-start; padding:16px 18px; border-radius:12px; margin-bottom:24px; background:var(--color-success-bg); color:var(--color-success)">
    <i class="bi bi-link-45deg" style="font-size:1.2rem"></i>
    <div style="flex:1">
      <strong>Enlace generado — cópialo y envíaselo al candidato:</strong>
      <div style="display:flex; gap:8px; margin-top:8px; align-items:center">
        <input type="text" readonly value="<?= e($origenAbsoluto . BASE_URL . '/contratacion?token=' . $nuevoToken) ?>"
               id="enlaceNuevo" style="flex:1; padding:8px 10px; border-radius:8px; border:1px solid var(--color-divider); font-size:12.5px">
        <button type="button" class="tag tag-accent" id="btnCopiarEnlace" style="border:none; cursor:pointer">
          <i class="bi bi-clipboard"></i> Copiar
        </button>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="section-head"><h4>Generar nuevo enlace</h4></div>
<form action="<?= BASE_URL ?>/contrataciones/generar" method="post" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:32px">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <input type="text" name="nombre_referencia" placeholder="Nombre del candidato (referencia interna)" style="flex:1 1 260px; padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider)">
  <select name="postulacion_id" style="padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider)">
    <option value="">Sin vincular a una postulación</option>
    <?php foreach ($postulacionesParaVincular as $p): ?>
      <option value="<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
  <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; opacity:.8">
    Vigencia (días, mínimo 15)
    <input type="number" name="dias_vigencia" value="15" min="15" style="width:70px; padding:9px 10px; border-radius:8px; border:1px solid var(--color-divider)">
  </label>
  <button type="submit" class="tag tag-neutral" style="border:none; cursor:pointer">
    <i class="bi bi-plus-lg"></i> Generar enlace
  </button>
</form>

<div class="section-head"><h4>Enlaces e información recibida</h4></div>
<?php if (!$contrataciones): ?>
  <p class="text-muted">Todavía no se ha generado ningún enlace.</p>
<?php else: foreach ($contrataciones as $c): ?>
  <div class="box-card" style="margin-bottom:16px; padding:18px">
    <?php if (!$c['usado']): ?>
      <?php $expirado = strtotime($c['expira_en']) < time(); ?>
      <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap">
        <div>
          <span class="tag <?= $expirado ? 'tag-danger' : 'tag-warning' ?>"><?= $expirado ? 'Expirado' : 'Pendiente' ?></span>
          <strong style="margin-left:8px"><?= e($c['nombre_referencia'] ?: 'Sin referencia') ?></strong>
          <?php if ($c['postulacion_nombre']): ?><span class="text-muted"> · vinculado a <?= e($c['postulacion_nombre']) ?></span><?php endif; ?>
          <span class="text-muted"> · vence <?= (new DateTime($c['expira_en']))->format('d/m/Y') ?></span>
        </div>
        <form action="<?= BASE_URL ?>/contrataciones/eliminar" method="post" onsubmit="return confirm('¿Eliminar este enlace sin usar?')">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <button type="submit" class="tag tag-danger" style="border:none; cursor:pointer"><i class="bi bi-trash"></i></button>
        </form>
      </div>
      <div style="margin-top:10px; font-size:12.5px; opacity:.7; word-break:break-all">
        <?= e($origenAbsoluto . BASE_URL . '/contratacion?token=' . $c['token']) ?>
      </div>
    <?php else: ?>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap">
        <div>
          <span class="tag tag-success">Completado</span>
          <strong style="margin-left:8px"><?= e($c['nombre']) ?></strong>
          <span class="text-muted"> · <?= e($c['cedula']) ?> · <?= e($c['celular']) ?> · <?= e($c['email']) ?></span>
        </div>
        <span class="text-muted" style="font-size:12px"><?= (new DateTime($c['completado_en']))->format('d/m/Y H:i') ?></span>
      </div>
      <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap">
        <?php foreach ($documentosPorContratacion[$c['id']] ?? [] as $doc): ?>
          <a class="tag tag-accent" href="<?= BASE_URL ?>/contrataciones/descargar?id=<?= (int) $doc['id'] ?>">
            <i class="bi bi-download"></i> <?= e($doc['tipo']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>

<script>
  var btnCopiar = document.getElementById('btnCopiarEnlace');
  if (btnCopiar) {
    btnCopiar.addEventListener('click', function () {
      var input = document.getElementById('enlaceNuevo');
      input.select();
      navigator.clipboard.writeText(input.value).then(function () {
        btnCopiar.innerHTML = '<i class="bi bi-check-lg"></i> Copiado';
        setTimeout(function () { btnCopiar.innerHTML = '<i class="bi bi-clipboard"></i> Copiar'; }, 2000);
      });
    });
  }
</script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
