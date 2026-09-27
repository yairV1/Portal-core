<?php $titulo = 'Permisos por rol'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>

<h1 class="page-title">Permisos por rol</h1>
<p class="page-desc">
  Decide qué módulos puede ver y usar cada rol — y, si hace falta más detalle, cada
  <strong>cargo</strong> (ver Panel de Usuarios). Un módulo/acción desmarcado se niega
  igual si viene del rol o del cargo de la persona: basta con que UNO de los dos lo niegue
  para que quede bloqueado. Un módulo desmarcado desaparece del menú y, si se entra por la
  URL directa, muestra "Acceso restringido" — igual que si no existiera para esa cuenta.
  El <strong>administrador global</strong> siempre ve todo, no aparece acá.
</p>

<?php if (isset($_GET['guardado'])): ?>
  <div class="alert alert-success" role="status">
    <i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>
    <div class="alert-content"><p class="alert-title">Permisos guardados</p></div>
    <button type="button" class="alert-close" data-dismiss="alert" aria-label="Cerrar aviso"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  </div>
<?php elseif (is_string($_GET['error'] ?? null)): ?>
  <?php
  $mensajesError = [
      'modulo'  => 'No se guardó nada: el formulario incluía un módulo que no existe.',
      'rol'     => 'No se guardó nada: el formulario incluía un rol que no se puede configurar (solo Administrador de dirección y Usuario).',
      'datos'   => 'No se guardó nada: los datos del formulario no son válidos.',
      'csrf'    => 'Tu sesión de formulario expiró, recarga la página e intenta de nuevo.',
      'guardar' => 'No se pudo guardar en la base de datos (¿falta aplicar la migración 045?). No se cambió nada.',
  ];
  $textoError = $mensajesError[$_GET['error']] ?? 'No se pudo guardar, intenta de nuevo.';
  ?>
  <div class="alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle-fill alert-icon" aria-hidden="true"></i>
    <div class="alert-content"><p class="alert-text"><?= e($textoError) ?></p></div>
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
          <?php foreach ($cargos as $c): ?>
            <th style="text-align:center"><?= e($c['nombre']) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php $seccionActual = null; foreach ($modulos as $clave => $m): ?>
          <?php if ($m['seccion'] !== $seccionActual): $seccionActual = $m['seccion']; ?>
            <tr>
              <td colspan="<?= 1 + count(PERMISOS_ROLES) + count($cargos) ?>" style="opacity:.6;font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;background:var(--color-surface)">
                <?= e($seccionActual) ?>
              </td>
            </tr>
          <?php endif; ?>
          <tr>
            <td><i class="bi bi-<?= e($m['icono'] ?: 'dot') ?>" style="opacity:.6;margin-right:8px"></i><?= e($m['label']) ?></td>
            <?php foreach (array_keys(PERMISOS_ROLES) as $rol): ?>
              <td style="text-align:center">
                <input type="checkbox" name="permitido[<?= e($clave) ?>][<?= e($rol) ?>]" value="1"
                  <?= empty($negados[$clave][$rol]) ? 'checked' : '' ?>>
              </td>
            <?php endforeach; ?>
            <?php foreach ($cargos as $c): ?>
              <?php if ((int) $m['id'] === 0) continue; ?>
              <td style="text-align:center">
                <input type="checkbox" name="permitido_cargo[<?= (int) $m['id'] ?>][<?= (int) $c['id'] ?>]" value="1"
                  <?= empty($negadosCargo[$m['id']][$c['id']]) ? 'checked' : '' ?>>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2 class="page-title" style="font-size:18px;margin-top:32px">Acciones por rol</h2>
  <p class="page-desc">
    Ajusta qué puede <strong>hacer</strong> cada rol dentro de un módulo al que ya tiene acceso
    (crear, subir, importar, eliminar...). La dirección/área asignada a cada usuario sigue
    aplicando igual; esto solo puede quitar una acción puntual, nunca dar acceso a otra dirección.
  </p>

  <div style="overflow-x:auto">
    <table class="table">
      <thead>
        <tr>
          <th>Acción</th>
          <?php foreach (PERMISOS_ROLES as $rolLabel): ?>
            <th style="text-align:center"><?= e($rolLabel) ?></th>
          <?php endforeach; ?>
          <?php foreach ($cargos as $c): ?>
            <th style="text-align:center"><?= e($c['nombre']) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach (ACCIONES_PERMISOS as $accionClave => $accionLabel): ?>
          <tr>
            <td><?= e($accionLabel) ?></td>
            <?php foreach (array_keys(PERMISOS_ROLES) as $rol): ?>
              <td style="text-align:center">
                <input type="checkbox" name="accion_permitida[<?= e($accionClave) ?>][<?= e($rol) ?>]" value="1"
                  <?= empty($accionesNegadas[$accionClave][$rol]) ? 'checked' : '' ?>>
              </td>
            <?php endforeach; ?>
            <?php foreach ($cargos as $c): ?>
              <td style="text-align:center">
                <input type="checkbox" name="accion_permitida_cargo[<?= e($accionClave) ?>][<?= (int) $c['id'] ?>]" value="1"
                  <?= empty($accionesNegadasCargo[$accionClave][$c['id']]) ? 'checked' : '' ?>>
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
