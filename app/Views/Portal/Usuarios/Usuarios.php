<?php $titulo = 'Usuarios'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<h1 class="page-title">Usuarios y roles</h1>
<p class="page-desc">
  Crea cuentas para las demás direcciones y decide qué puede administrar cada una. Un <strong>administrador de dirección</strong> solo puede crear, subir y eliminar carpetas/documentos DENTRO de la dirección que le asignes acá — el resto del portal (Contenido Landing, Contrataciones, Calendario, etc.) sigue siendo exclusivo del administrador global.
  Si le asignas un <strong>área de trabajo</strong> a un <strong>usuario</strong> normal, esa persona deja de ver las demás direcciones (ni la tarjeta en "Todos los módulos" ni la página si entra por la URL directa) — solo ve la suya. Sin área asignada, sigue viendo todo el portal como hasta ahora.
</p>

<?php
$ROL_LABEL = [
    'admin'           => ['Administrador global', 'tag-accent'],
    'admin_direccion' => ['Administrador de dirección', 'tag-info'],
    'usuario'         => ['Usuario (solo lectura)', 'tag'],
];
$campoTexto = function (string $name, string $valor = '', string $placeholder = '', string $tipo = 'text', bool $required = false) {
    ?>
    <input type="<?= e($tipo) ?>" name="<?= e($name) ?>" value="<?= e($valor) ?>" placeholder="<?= e($placeholder) ?>" <?= $required ? 'required' : '' ?>
           style="flex:1 1 200px; padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); background:var(--color-bg); color:var(--color-text)">
    <?php
};
$campoSelectRol = function (string $name, string $valor, string $claseJs) use ($ROL_LABEL) {
    ?>
    <select name="<?= e($name) ?>" class="<?= e($claseJs) ?>" required
            style="flex:1 1 200px; padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); background:var(--color-bg); color:var(--color-text)">
      <?php foreach ($ROL_LABEL as $valorRol => $info): ?>
        <option value="<?= e($valorRol) ?>" <?= $valorRol === $valor ? 'selected' : '' ?>><?= e($info[0]) ?></option>
      <?php endforeach; ?>
    </select>
    <?php
};
$campoSelectDireccion = function (string $name, ?int $valor, string $claseJs) use ($direccionesDisponibles) {
    ?>
    <select name="<?= e($name) ?>" class="<?= e($claseJs) ?>"
            style="flex:1 1 220px; padding:9px 12px; border-radius:8px; border:1px solid var(--color-divider); background:var(--color-bg); color:var(--color-text)">
      <option value="">— Sin área asignada (ve todo) —</option>
      <?php foreach ($direccionesDisponibles as $d): ?>
        <option value="<?= (int) $d['id'] ?>" <?= (int) $d['id'] === $valor ? 'selected' : '' ?>><?= e($d['titulo']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php
};
?>

<div class="section-head"><h4>Nuevo usuario</h4></div>
<form action="<?= BASE_URL ?>/usuarios/crear" method="post" class="usuarios-form" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:30px;align-items:flex-start">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
  <?php $campoTexto('nombre', '', 'Nombre completo', 'text', true); ?>
  <?php $campoTexto('correo', '', 'correo@coreducacion.edu.co', 'email', true); ?>
  <?php $campoTexto('cargo', '', 'Cargo (opcional)'); ?>
  <?php $campoTexto('password', '', 'Contraseña (mín. 8 caracteres)', 'password', true); ?>
  <?php $campoSelectRol('rol', 'usuario', 'usuarios-rol-nuevo'); ?>
  <?php $campoSelectDireccion('direccion_id', null, 'usuarios-direccion-nuevo'); ?>
  <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i> Crear usuario</button>
</form>

<div class="section-head"><h4>Todos los usuarios</h4></div>
<?php if (!$usuarios): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-people"></i></div>
    <h4>No hay usuarios</h4>
    <p>Crea el primero con el formulario de arriba.</p>
  </div>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Nombre</th><th>Correo</th><th>Cargo</th><th>Rol</th><th>Creado</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($usuarios as $u): [$rolLabel, $rolClase] = $ROL_LABEL[$u['rol']] ?? ['Desconocido', 'tag']; ?>
        <tr>
          <td><strong><?= e($u['nombre']) ?></strong><?= (int) $u['id'] === (int) $_SESSION['usuario_id'] ? ' <span class="text-muted">(tú)</span>' : '' ?></td>
          <td style="opacity:.75"><?= e($u['correo']) ?></td>
          <td style="opacity:.75"><?= e($u['cargo'] ?? '—') ?></td>
          <td>
            <span class="tag <?= e($rolClase) ?>"><?= e($rolLabel) ?></span>
            <?php if ($u['direccion_id'] !== null): ?>
              <span class="tag" style="margin-left:4px" title="Solo ve esta dirección"><i class="bi bi-eye"></i> <?= e($u['direccion_titulo'] ?? 'sin dirección') ?></span>
            <?php endif; ?>
          </td>
          <td style="opacity:.6"><?= e((new DateTime($u['creado_en']))->format('d/m/Y')) ?></td>
          <td style="white-space:nowrap">
            <details class="usuarios-editar-details">
              <summary class="btn" style="display:inline-flex;cursor:pointer"><i class="bi bi-pencil"></i> Editar</summary>
              <form action="<?= BASE_URL ?>/usuarios/editar" method="post" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;padding:14px;border:1px solid var(--color-divider);border-radius:10px;background:var(--color-surface)">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <?php $campoTexto('nombre', $u['nombre'], 'Nombre completo', 'text', true); ?>
                <?php $campoTexto('correo', $u['correo'], 'Correo', 'email', true); ?>
                <?php $campoTexto('cargo', $u['cargo'] ?? '', 'Cargo (opcional)'); ?>
                <?php $campoTexto('password', '', 'Nueva contraseña (déjalo vacío para no cambiarla)', 'password'); ?>
                <?php $campoSelectRol('rol', $u['rol'], 'usuarios-rol-' . (int) $u['id']); ?>
                <?php $campoSelectDireccion('direccion_id', $u['direccion_id'] !== null ? (int) $u['direccion_id'] : null, 'usuarios-direccion-' . (int) $u['id']); ?>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar cambios</button>
              </form>
            </details>
            <?php if ((int) $u['id'] !== (int) $_SESSION['usuario_id']): ?>
              <form action="<?= BASE_URL ?>/usuarios/eliminar" method="post" style="display:inline-block" onsubmit="return confirm('¿Eliminar a <?= e(addslashes($u['nombre'])) ?>? No podrá volver a iniciar sesión.')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <button type="submit" class="btn btn-danger btn-icon" aria-label="Eliminar usuario"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<script>
  // El selector de dirección no tiene sentido para 'admin' (siempre ve/
  // administra todo el portal) — se oculta solo para ese rol. Para
  // 'admin_direccion' y 'usuario' sí aplica (obligatorio en el primero,
  // opcional en el segundo — ver UsuariosController.php); queda igual de
  // funcional sin JS, esto solo evita confundir con un campo que no aplica.
  document.addEventListener('DOMContentLoaded', function () {
    function conectar(rolSelect) {
      var sufijo = rolSelect.className.replace('usuarios-rol-', '');
      var direccionSelect = document.querySelector('.usuarios-direccion-' + sufijo);
      if (!direccionSelect) return;
      function actualizar() {
        direccionSelect.style.display = rolSelect.value === 'admin' ? 'none' : '';
      }
      rolSelect.addEventListener('change', actualizar);
      actualizar();
    }
    document.querySelectorAll('[class*="usuarios-rol-"]').forEach(conectar);
  });
</script>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
