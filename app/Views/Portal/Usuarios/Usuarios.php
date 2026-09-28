<?php $titulo = 'Usuarios'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<?php
/** Variables que llegan desde UsuariosController.php:
 * @var array $usuarios
 * @var array $direccionesDisponibles
 * @var array $cargosDisponibles
 */
$ROL_LABEL = [
    'admin'           => ['Administrador global', 'badge-primary'],
    'admin_direccion' => ['Administrador de dirección', 'badge-info'],
    'usuario'         => ['Usuario (solo lectura)', 'badge-neutral'],
];
$miId = (int) $_SESSION['usuario_id'];

// Campos de "Rol y acceso" — idénticos en crear y editar (mismos name= que
// siempre lee UsuariosController.php); $pref distingue los id de cada drawer.
$camposAcceso = function (string $pref, string $rolActual = 'usuario') use ($ROL_LABEL, $direccionesDisponibles, $cargosDisponibles) {
    ?>
    <section class="form-section">
      <h3 class="form-section-title">Rol y acceso</h3>
      <p class="form-section-desc">Define qué puede ver y administrar esta cuenta.</p>
      <div class="form-stack">
        <div class="field">
          <label class="field-label" for="<?= $pref ?>Rol">Rol <span class="req" aria-hidden="true">*</span></label>
          <select class="select" id="<?= $pref ?>Rol" name="rol" required data-rol-select>
            <?php foreach ($ROL_LABEL as $valorRol => $info): ?>
              <option value="<?= e($valorRol) ?>" <?= $valorRol === $rolActual ? 'selected' : '' ?>><?= e($info[0]) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="field-hint">
            <strong>Administrador de dirección:</strong> crea, sube y elimina carpetas y documentos solo dentro de su dirección.
            <strong>Administrador global:</strong> administra todo el portal (Contenido landing, Contrataciones, Calendario…).
          </p>
        </div>
        <div class="field" data-campo-direccion>
          <label class="field-label" for="<?= $pref ?>Direccion">Área de trabajo</label>
          <select class="select" id="<?= $pref ?>Direccion" name="direccion_id">
            <option value="">— Sin área asignada (ve todo) —</option>
            <?php foreach ($direccionesDisponibles as $d): ?>
              <option value="<?= (int) $d['id'] ?>"><?= e($d['titulo']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="field-hint">Obligatoria para un administrador de dirección. Si asignas un área a un usuario normal, solo verá esa dirección; sin área, ve todo el portal.</p>
        </div>
        <div class="field">
          <label class="field-label" for="<?= $pref ?>Cargo">Cargo <span class="opt">(opcional)</span></label>
          <select class="select" id="<?= $pref ?>Cargo" name="cargo_id" data-cargo-select>
            <option value="">— Sin cargo —</option>
            <?php foreach ($cargosDisponibles as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
            <option value="__nuevo__">+ Agregar nuevo cargo…</option>
          </select>
        </div>
        <div class="field" data-campo-cargo-nuevo hidden>
          <label class="field-label" for="<?= $pref ?>CargoNuevo">Nombre del cargo nuevo</label>
          <input class="input" type="text" id="<?= $pref ?>CargoNuevo" name="cargo_nuevo" placeholder="Ej. Coordinador de calidad">
          <p class="field-hint">Se agrega al catálogo de cargos y queda disponible para otros usuarios.</p>
        </div>
      </div>
    </section>
    <?php
};
?>

<?php ui_page_header([
    'title'   => 'Usuarios y roles',
    'desc'    => 'Crea cuentas para las direcciones y decide qué puede ver y administrar cada una.',
    'actions' => function () { ?>
        <button type="button" class="btn btn-primary" data-open="usuarioNuevo" aria-haspopup="dialog"><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button>
    <?php },
]); ?>

<?php if (!$usuarios): ?>
  <?php ui_empty_state([
      'icon' => 'people', 'title' => 'Aún no hay usuarios',
      'text' => 'Crea la primera cuenta para darle acceso a una dirección.',
      'actions' => function () { ?><button type="button" class="btn btn-primary" data-open="usuarioNuevo"><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button><?php },
  ]); ?>
<?php else: ?>
  <div class="filter-bar">
    <div class="input-group search-field">
      <i class="bi bi-search input-icon" aria-hidden="true"></i>
      <input class="input" type="search" placeholder="Buscar por nombre, correo o cargo" aria-label="Buscar usuarios" data-table-filter="tablaUsuarios">
    </div>
    <span class="filter-bar-end text-muted"><?= count($usuarios) ?> usuario<?= count($usuarios) === 1 ? '' : 's' ?></span>
  </div>

  <div class="table-wrap">
    <table class="table table--stack" id="tablaUsuarios">
      <thead><tr><th>Nombre</th><th>Correo</th><th>Cargo</th><th>Rol</th><th>Creado</th><th class="col-actions"><span class="sr-only">Acciones</span></th></tr></thead>
      <tbody>
        <?php foreach ($usuarios as $u): [$rolLabel, $rolClase] = $ROL_LABEL[$u['rol']] ?? ['Desconocido', 'badge-neutral']; $esYo = (int) $u['id'] === $miId; ?>
          <tr>
            <td class="cell-strong"><?= e($u['nombre']) ?><?php if ($esYo): ?> <span class="badge badge-neutral">Tú</span><?php endif; ?></td>
            <td class="cell-muted"><?= e($u['correo']) ?></td>
            <td class="cell-muted"><?= e($u['cargo_nombre'] ?? '—') ?></td>
            <td>
              <span class="badge <?= e($rolClase) ?>"><?= e($rolLabel) ?></span>
              <?php if ($u['direccion_id'] !== null): ?>
                <span class="badge badge-outline" title="Solo ve esta dirección"><i class="bi bi-eye" aria-hidden="true"></i> <?= e($u['direccion_titulo'] ?? 'sin dirección') ?></span>
              <?php endif; ?>
            </td>
            <td class="cell-muted"><?= e((new DateTime($u['creado_en']))->format('d/m/Y')) ?></td>
            <td class="col-actions">
              <div class="table-actions">
                <button type="button" class="btn btn-sm" data-editar-usuario
                  data-id="<?= (int) $u['id'] ?>"
                  data-nombre="<?= e($u['nombre']) ?>"
                  data-correo="<?= e($u['correo']) ?>"
                  data-cargo="<?= $u['cargo_id'] !== null ? (int) $u['cargo_id'] : '' ?>"
                  data-rol="<?= e($u['rol']) ?>"
                  data-direccion="<?= $u['direccion_id'] !== null ? (int) $u['direccion_id'] : '' ?>">
                  <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                </button>
                <?php if (!$esYo): ?>
                  <form action="<?= BASE_URL ?>/usuarios/eliminar" method="post" data-confirm="¿Eliminar a <?= e($u['nombre']) ?>?" data-confirm-text="No podrá volver a iniciar sesión." data-confirm-ok="Eliminar">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" aria-label="Eliminar a <?= e($u['nombre']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="empty-state empty-state--compact empty-state--bare" data-filter-empty="tablaUsuarios" hidden>
      <div class="ic" aria-hidden="true"><i class="bi bi-search" aria-hidden="true"></i></div><p>Ningún usuario coincide con la búsqueda.</p>
    </div>
  </div>
<?php endif; ?>

<!-- ── Nuevo usuario ── -->
<dialog class="drawer" id="usuarioNuevo" aria-labelledby="usuarioNuevoT">
  <form action="<?= BASE_URL ?>/usuarios/crear" method="post" data-usuario-form>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="usuarioNuevoT">Nuevo usuario</h2>
        <p class="modal-desc">La persona entra con este correo y contraseña.</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body">
      <section class="form-section">
        <h3 class="form-section-title">Cuenta</h3>
        <div class="form-stack">
          <div class="field">
            <label class="field-label" for="unNombre">Nombre completo <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="text" id="unNombre" name="nombre" required autocomplete="off">
          </div>
          <div class="field">
            <label class="field-label" for="unCorreo">Correo <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="email" id="unCorreo" name="correo" required placeholder="correo@coreducacion.edu.co" autocomplete="off">
          </div>
          <div class="field">
            <label class="field-label" for="unPassword">Contraseña <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="password" id="unPassword" name="password" required minlength="8" autocomplete="new-password">
            <p class="field-hint">Mínimo 8 caracteres.</p>
          </div>
        </div>
      </section>
      <?php $camposAcceso('un'); ?>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Crear usuario</button>
    </footer>
  </form>
</dialog>

<!-- ── Editar usuario (uno solo; se llena con los datos de la fila) ── -->
<dialog class="drawer" id="usuarioEditar" aria-labelledby="usuarioEditarT">
  <form action="<?= BASE_URL ?>/usuarios/editar" method="post" data-usuario-form>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" id="ueId">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="usuarioEditarT">Editar usuario</h2>
        <p class="modal-desc" id="ueDesc"></p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body">
      <section class="form-section">
        <h3 class="form-section-title">Cuenta</h3>
        <div class="form-stack">
          <div class="field">
            <label class="field-label" for="ueNombre">Nombre completo <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="text" id="ueNombre" name="nombre" required autocomplete="off">
          </div>
          <div class="field">
            <label class="field-label" for="ueCorreo">Correo <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="email" id="ueCorreo" name="correo" required autocomplete="off">
          </div>
          <div class="field">
            <label class="field-label" for="uePassword">Nueva contraseña <span class="opt">(opcional)</span></label>
            <input class="input" type="password" id="uePassword" name="password" minlength="8" autocomplete="new-password">
            <p class="field-hint">Déjala vacía para no cambiarla. Mínimo 8 caracteres.</p>
          </div>
        </div>
      </section>
      <?php $camposAcceso('ue'); ?>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
  </form>
</dialog>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Dentro de cada formulario: el área de trabajo no aplica al admin
    // global (siempre administra todo) y "+ Agregar nuevo cargo…" revela el
    // campo de texto. Sin JS ambos campos quedan visibles y funcionales.
    function sincronizar(form) {
      var rol = form.querySelector('[data-rol-select]');
      var cargo = form.querySelector('[data-cargo-select]');
      var campoDir = form.querySelector('[data-campo-direccion]');
      var campoNuevo = form.querySelector('[data-campo-cargo-nuevo]');
      if (rol && campoDir) campoDir.hidden = rol.value === 'admin';
      if (cargo && campoNuevo) campoNuevo.hidden = cargo.value !== '__nuevo__';
    }
    document.querySelectorAll('[data-usuario-form]').forEach(function (form) {
      form.addEventListener('change', function () { sincronizar(form); });
      sincronizar(form);
    });

    // "Editar" de una fila: llena el drawer compartido y lo abre.
    var drawer = document.getElementById('usuarioEditar');
    document.querySelectorAll('[data-editar-usuario]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        var form = drawer.querySelector('form');
        form.reset();
        document.getElementById('ueId').value = d.id;
        document.getElementById('ueNombre').value = d.nombre;
        document.getElementById('ueCorreo').value = d.correo;
        document.getElementById('ueRol').value = d.rol;
        document.getElementById('ueDireccion').value = d.direccion;
        document.getElementById('ueCargo').value = d.cargo;
        document.getElementById('ueDesc').textContent = d.nombre + ' · ' + d.correo;
        sincronizar(form);
        UI.open(drawer, btn);
      });
    });
  });
</script>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
