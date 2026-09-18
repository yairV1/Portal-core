<?php $titulo = 'Permisos por rol'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>

<h1 class="page-title">Permisos por rol</h1>
<p class="page-desc">
  Decide qué módulos puede ver y usar cada rol. Un módulo desmarcado desaparece del menú de ese rol
  y, si entra por la URL directa, le muestra "Acceso restringido" — igual que si no existiera para
  esa cuenta. El <strong>administrador global</strong> siempre ve todo, no aparece acá.
</p>

<?php if (isset($_GET['guardado'])): ?>
  <div class="empty-state" style="margin:0 0 24px;padding:16px;text-align:left;max-width:none">
    <p style="margin:0"><i class="bi bi-check-circle-fill" style="color:var(--color-success)"></i> Permisos guardados.</p>
  </div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="empty-state" style="margin:0 0 24px;padding:16px;text-align:left;max-width:none">
    <p style="margin:0"><i class="bi bi-exclamation-triangle-fill" style="color:var(--color-accent-2)"></i> No se pudo guardar, intenta de nuevo.</p>
  </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/permisos-por-rol/guardar" method="post">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

  <div style="overflow-x:auto">
    <table class="table">
      <thead>
        <tr>
          <th>Módulo</th>
          <?php foreach (PERMISOS_ROLES as $rolLabel): ?>
            <th style="text-align:center"><?= e($rolLabel) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php $seccionActual = null; foreach ($modulos as $m): ?>
          <?php if ($m['seccion'] !== $seccionActual): $seccionActual = $m['seccion']; ?>
            <tr>
              <td colspan="<?= 1 + count(PERMISOS_ROLES) ?>" style="opacity:.6;font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;background:var(--color-surface)">
                <?= e($seccionActual) ?>
              </td>
            </tr>
          <?php endif; ?>
          <tr>
            <td><i class="bi bi-<?= e($m['icono'] ?: 'dot') ?>" style="opacity:.6;margin-right:8px"></i><?= e($m['label']) ?></td>
            <?php foreach (array_keys(PERMISOS_ROLES) as $rol): ?>
              <td style="text-align:center">
                <input type="checkbox" name="permitido[<?= (int) $m['id'] ?>][<?= e($rol) ?>]" value="1"
                  <?= empty($negados[$m['id']][$rol]) ? 'checked' : '' ?>>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <button type="submit" class="doc-btn doc-btn--primary" style="margin-top:20px"><i class="bi bi-check-lg"></i> Guardar permisos</button>
</form>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
