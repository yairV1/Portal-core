<?php
/**
 * Listado de empleados + un documento por empleado (Hojas de vida,
 * Contratos, Certificaciones laborales). Antes eran 3 vistas casi idénticas
 * con la edición abierta DENTRO de la celda de la tabla (<details>); ahora
 * es un solo parcial con:
 *   - "Nuevo empleado" y "Editar empleado" en drawers,
 *   - "Subir <documento>" en un modal,
 *   - eliminar con el diálogo de confirmación del sistema.
 * Mismos endpoints y los mismos name= que leen EmpleadoController.php y
 * EmpleadoDocumentoController.php.
 *
 * La vista que lo incluye define $ED:
 *   'titulo', 'desc', 'icono', 'columna' (encabezado de la columna),
 *   'segmento' (hojas-de-vida | contratos | certificaciones-laborales),
 *   'singular' (ej. "hoja de vida"), 'confirmar' (título al eliminar),
 *   'resumen'  => callable(array $emp): imprime el resumen del documento,
 *   'campos'   => callable(): imprime los campos propios del documento.
 * Variables del controlador: $empleados, $puedeAdministrar, $rutaBase, $csrf.
 */
$urlDoc = BASE_URL . '/talento-humano/' . $ED['segmento'];
?>

<?php ui_page_header([
    'title'   => $ED['titulo'],
    'desc'    => $ED['desc'],
    'eyebrow' => 'Talento Humano',
    'back'    => ['href' => BASE_URL . '/talento-humano', 'label' => 'Volver a Talento Humano'],
    'actions' => $puedeAdministrar ? function () { ?>
        <button type="button" class="btn btn-primary" data-open="empleadoNuevo" aria-haspopup="dialog"><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo empleado</button>
    <?php } : null,
]); ?>

<?php if (!$empleados): ?>
  <?php ui_empty_state([
      'icon'    => $ED['icono'],
      'title'   => 'Aún no hay empleados',
      'text'    => $puedeAdministrar ? 'Agrega el primero para empezar a adjuntar su ' . $ED['singular'] . '.' : 'Todavía no se ha registrado ningún empleado.',
      'actions' => $puedeAdministrar ? function () { ?><button type="button" class="btn btn-primary" data-open="empleadoNuevo"><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo empleado</button><?php } : null,
  ]); ?>
<?php else: ?>
  <div class="filter-bar">
    <div class="input-group search-field">
      <i class="bi bi-search input-icon" aria-hidden="true"></i>
      <input class="input" type="search" placeholder="Buscar por nombre, documento o cargo" aria-label="Buscar empleados" data-table-filter="tablaEmpleados">
    </div>
    <span class="filter-bar-end text-muted"><?= count($empleados) ?> empleado<?= count($empleados) === 1 ? '' : 's' ?></span>
  </div>

  <div class="table-wrap">
    <table class="table table--stack table--stack-md" id="tablaEmpleados">
      <thead><tr><th>Nombre</th><th>Documento</th><th>Cargo</th><th><?= e($ED['columna']) ?></th><th class="col-actions"><span class="sr-only">Acciones</span></th></tr></thead>
      <tbody>
        <?php foreach ($empleados as $emp): $tieneDoc = $emp['doc_id'] && $emp['doc_archivo']; ?>
          <tr>
            <td class="cell-strong">
              <?= e($emp['nombre_completo']) ?>
              <?php if (($emp['estado'] ?? 'activo') === 'inactivo'): ?> <span class="badge badge-neutral">Inactivo</span><?php endif; ?>
            </td>
            <td class="cell-muted"><?= e($emp['documento']) ?></td>
            <td class="cell-muted"><?= e($emp['cargo'] ?: '—') ?></td>
            <td>
              <?php if ($tieneDoc): ?>
                <div class="emp-doc">
                  <div class="emp-doc-info"><?php ($ED['resumen'])($emp); ?></div>
                  <div class="table-actions">
                    <a class="btn btn-sm btn-icon" href="<?= $urlDoc ?>/descargar?id=<?= (int) $emp['doc_id'] ?>" target="_blank" rel="noopener" aria-label="Descargar <?= e($ED['singular']) ?>" title="Descargar"><i class="bi bi-download" aria-hidden="true"></i></a>
                    <?php if ($puedeAdministrar): ?>
                      <form action="<?= $urlDoc ?>/eliminar" method="post" data-confirm="<?= e($ED['confirmar']) ?>" data-confirm-text="<?= e($emp['nombre_completo']) ?>" data-confirm-ok="Eliminar">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                        <input type="hidden" name="doc_id" value="<?= (int) $emp['doc_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" aria-label="Eliminar <?= e($ED['singular']) ?>" title="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              <?php elseif ($puedeAdministrar): ?>
                <button type="button" class="btn btn-sm" data-subir-doc data-id="<?= (int) $emp['id'] ?>" data-nombre="<?= e($emp['nombre_completo']) ?>">
                  <i class="bi bi-upload" aria-hidden="true"></i> Subir
                </button>
              <?php else: ?>
                <span class="badge badge-warning badge-dot">Sin subir</span>
              <?php endif; ?>
            </td>
            <td class="col-actions">
              <?php if ($puedeAdministrar): ?>
                <div class="table-actions">
                  <button type="button" class="btn btn-sm" data-editar-empleado
                    data-id="<?= (int) $emp['id'] ?>"
                    data-nombre="<?= e($emp['nombre_completo']) ?>"
                    data-documento="<?= e($emp['documento']) ?>"
                    data-cargo="<?= e($emp['cargo'] ?? '') ?>"
                    data-telefono="<?= e($emp['telefono'] ?? '') ?>"
                    data-correo="<?= e($emp['correo'] ?? '') ?>"
                    data-ingreso="<?= e($emp['fecha_ingreso'] ?? '') ?>"
                    data-estado="<?= e($emp['estado'] ?? 'activo') ?>">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                  </button>
                  <form action="<?= BASE_URL ?>/talento-humano/empleados/eliminar" method="post" data-confirm="¿Eliminar a <?= e($emp['nombre_completo']) ?>?" data-confirm-text="También se borran su hoja de vida, contrato y certificaciones." data-confirm-ok="Eliminar">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
                    <input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" aria-label="Eliminar a <?= e($emp['nombre_completo']) ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
                  </form>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="empty-state empty-state--compact empty-state--bare" data-filter-empty="tablaEmpleados" hidden>
      <div class="ic" aria-hidden="true"><i class="bi bi-search" aria-hidden="true"></i></div><p>Ningún empleado coincide con la búsqueda.</p>
    </div>
  </div>
<?php endif; ?>

<?php if ($puedeAdministrar): ?>
<!-- ── Nuevo empleado ── -->
<dialog class="drawer drawer-sm" id="empleadoNuevo" aria-labelledby="empleadoNuevoT">
  <form action="<?= BASE_URL ?>/talento-humano/empleados/crear" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="empleadoNuevoT">Nuevo empleado</h2>
        <p class="modal-desc">Después podrás adjuntarle hoja de vida, contrato y certificaciones.</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="enNombre">Nombre completo <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="enNombre" name="nombre_completo" required>
      </div>
      <div class="field">
        <label class="field-label" for="enDocumento">Documento de identidad <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="enDocumento" name="documento" required inputmode="numeric">
      </div>
      <div class="field">
        <label class="field-label" for="enCargo">Cargo <span class="opt">(opcional)</span></label>
        <input class="input" type="text" id="enCargo" name="cargo">
      </div>
      <div class="field">
        <label class="field-label" for="enIngreso">Fecha de ingreso <span class="opt">(opcional)</span></label>
        <input class="input" type="date" id="enIngreso" name="fecha_ingreso">
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Agregar empleado</button>
    </footer>
  </form>
</dialog>

<!-- ── Editar empleado (uno solo; se llena con los datos de la fila) ── -->
<dialog class="drawer" id="empleadoEditar" aria-labelledby="empleadoEditarT">
  <form action="<?= BASE_URL ?>/talento-humano/empleados/editar" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="volver" value="<?= e($rutaBase) ?>">
    <input type="hidden" name="id" id="eeId">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="empleadoEditarT">Editar empleado</h2>
        <p class="modal-desc" id="eeDesc"></p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body">
      <section class="form-section">
        <h3 class="form-section-title">Datos personales</h3>
        <div class="form-grid">
          <div class="field field--full">
            <label class="field-label" for="eeNombre">Nombre completo <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="text" id="eeNombre" name="nombre_completo" required>
          </div>
          <div class="field">
            <label class="field-label" for="eeDocumento">Documento <span class="req" aria-hidden="true">*</span></label>
            <input class="input" type="text" id="eeDocumento" name="documento" required inputmode="numeric">
          </div>
          <div class="field">
            <label class="field-label" for="eeTelefono">Teléfono</label>
            <input class="input" type="text" id="eeTelefono" name="telefono" inputmode="tel">
          </div>
          <div class="field field--full">
            <label class="field-label" for="eeCorreo">Correo</label>
            <input class="input" type="email" id="eeCorreo" name="correo">
          </div>
        </div>
      </section>
      <section class="form-section">
        <h3 class="form-section-title">Vinculación</h3>
        <div class="form-grid">
          <div class="field field--full">
            <label class="field-label" for="eeCargo">Cargo</label>
            <input class="input" type="text" id="eeCargo" name="cargo">
          </div>
          <div class="field">
            <label class="field-label" for="eeIngreso">Fecha de ingreso</label>
            <input class="input" type="date" id="eeIngreso" name="fecha_ingreso">
          </div>
          <div class="field">
            <label class="field-label" for="eeEstado">Estado</label>
            <select class="select" id="eeEstado" name="estado">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
        </div>
      </section>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
  </form>
</dialog>

<!-- ── Subir documento (uno solo; se llena con el empleado de la fila) ── -->
<dialog class="modal" id="docSubir" aria-labelledby="docSubirT">
  <form action="<?= $urlDoc ?>/subir" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="empleado_id" id="dsEmpleado">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="docSubirT">Subir <?= e($ED['singular']) ?></h2>
        <p class="modal-desc" id="dsDesc"></p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="dsNombre">Nombre del documento <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="dsNombre" name="nombre" required>
      </div>
      <?php ($ED['campos'])(); ?>
      <div class="field">
        <span class="field-label" id="dsArchivoL">Archivo <span class="req" aria-hidden="true">*</span></span>
        <label class="file-drop">
          <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
          <span><strong>Elige un archivo</strong> o arrástralo aquí</span>
          <span class="file-drop-name"></span>
          <input type="file" name="archivo" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" aria-labelledby="dsArchivoL">
        </label>
        <p class="field-hint">PDF, Word, Excel o PowerPoint · máximo 20 MB</p>
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-upload" aria-hidden="true"></i> Subir</button>
    </footer>
  </form>
</dialog>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var editar = document.getElementById('empleadoEditar');
    document.querySelectorAll('[data-editar-empleado]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        editar.querySelector('form').reset();
        document.getElementById('eeId').value = d.id;
        document.getElementById('eeNombre').value = d.nombre;
        document.getElementById('eeDocumento').value = d.documento;
        document.getElementById('eeCargo').value = d.cargo;
        document.getElementById('eeTelefono').value = d.telefono;
        document.getElementById('eeCorreo').value = d.correo;
        document.getElementById('eeIngreso').value = d.ingreso;
        document.getElementById('eeEstado').value = d.estado || 'activo';
        document.getElementById('eeDesc').textContent = d.nombre + ' · ' + d.documento;
        UI.open(editar, btn);
      });
    });
    var subir = document.getElementById('docSubir');
    document.querySelectorAll('[data-subir-doc]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = subir.querySelector('form');
        form.reset();
        form.querySelectorAll('.file-drop').forEach(function (z) { z.classList.remove('has-file'); var n = z.querySelector('.file-drop-name'); if (n) n.textContent = ''; });
        document.getElementById('dsEmpleado').value = btn.dataset.id;
        document.getElementById('dsDesc').textContent = btn.dataset.nombre;
        UI.open(subir, btn);
      });
    });
  });
</script>
<?php endif; ?>
