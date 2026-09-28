<?php
/**
 * Página resumen de un módulo (Novedades y las 4 perspectivas del Cuadro de
 * Mando Integral — antes 2 vistas casi idénticas): indicadores, áreas,
 * documentación destacada y columna lateral.
 *
 * La vista define $MR:
 *   'volver'      => valor del campo "volver" de los formularios (ruta)
 *   'areasTitulo' => encabezado de la sección de áreas
 *   'back'        => opcional ['href' => …, 'label' => …]
 * Variables de PortalController.php: $direccion, $moduloKicker, $moduloTitulo,
 * $moduloDesc, $moduloKpis, $moduloAreas, $moduloDocumentos,
 * $moduloResponsables, $moduloSoftware y, si existen, $moduloNoticias y
 * $moduloEventos (Novedades).
 */
$esAdminMod = $direccion && usuario_admin_de($direccion['id']);
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">

<?php ui_page_header([
    'title'   => $moduloTitulo ?: ($titulo ?? ''),
    'desc'    => $moduloDesc ?: null,
    'eyebrow' => $moduloKicker ?: null,
    'back'    => $MR['back'] ?? null,
]); ?>

<div class="kpis modulo-resumen-kpis">
  <?php if (!$moduloKpis): ?>
    <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></div><p>Sin indicadores por ahora.</p></div>
  <?php else: foreach ($moduloKpis as $k): ?>
    <div class="kpi">
      <div class="kpi-head"><div class="kpi-label"><?= e($k['label']) ?></div></div>
      <div class="kpi-value"><?= e($k['valor']) ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="modulo-grid">
  <div>
    <section>
      <div class="section-header section-header--spaced"><h2 class="section-heading"><?= e($MR['areasTitulo']) ?></h2></div>
      <?php if (!$moduloAreas): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></div><p>Sin áreas registradas por ahora.</p></div>
      <?php else: ?>
        <div class="area-lista">
          <?php foreach ($moduloAreas as $a): ?>
            <div class="area-item" id="<?= e(sb_slug($a['label'])) ?>">
              <span class="ic" aria-hidden="true"><i class="bi bi-folder2" aria-hidden="true"></i></span>
              <span class="doc-flex-grow">
                <span class="label"><?= e($a['label']) ?></span>
                <span class="meta"><?= e($a['meta']) ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section>
      <div class="section-header section-header--spaced">
        <h2 class="section-heading">Documentación destacada</h2>
        <?php if ($esAdminMod): ?>
          <button type="button" class="btn btn-sm btn-primary" data-open="docNuevo" aria-haspopup="dialog"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
        <?php endif; ?>
      </div>
      <?php if (!$moduloDocumentos): ?>
        <?php ui_empty_state(['icon' => 'file-earmark-text', 'title' => 'Sin documentos por ahora', 'text' => $esAdminMod ? 'Agrega el primero con el botón de arriba.' : null, 'compact' => true]); ?>
      <?php else: ?>
        <div class="table-wrap">
          <table class="table table--stack">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Versión</th><th>Actualizado</th><th class="col-actions">Archivo</th></tr></thead>
            <tbody>
              <?php foreach ($moduloDocumentos as $d): ?>
                <tr>
                  <td class="cell-strong"><?= e($d['nombre']) ?></td>
                  <td class="cell-muted"><?= e($d['tipo'] ?: '—') ?></td>
                  <td><?php if ($d['version']): ?><span class="tag-stamp"><?= e($d['version']) ?></span><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                  <td class="cell-muted"><?= e($d['fecha']) ?></td>
                  <td class="col-actions">
                    <?php if ($d['archivo']): ?>
                      <a class="btn btn-sm" href="<?= BASE_URL ?>/documentos/descargar?tipo=direccion&id=<?= (int) $d['id'] ?>"><i class="bi bi-download" aria-hidden="true"></i> Descargar</a>
                    <?php elseif (usuario_admin_de($direccion['id'] ?? null)): ?>
                      <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                        <input type="hidden" name="tipo" value="direccion">
                        <input type="hidden" name="volver" value="<?= e($MR['volver']) ?>">
                        <input type="hidden" name="archivo_id" value="<?= (int) $d['id'] ?>">
                        <label class="file-btn btn btn-sm"><i class="bi bi-upload" aria-hidden="true"></i> Subir archivo
                          <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required onchange="this.form.submit()" aria-label="Subir archivo para <?= e($d['nombre']) ?>">
                        </label>
                      </form>
                    <?php else: ?>
                      <span class="badge badge-neutral">Sin archivo</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <aside class="doc-side-stack">
    <?php if (isset($moduloNoticias)): ?>
      <section class="box-card box-card--flat box-card--compact">
        <h2 class="side-box-title">Últimas noticias</h2>
        <?php if (!$moduloNoticias): ?>
          <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-newspaper" aria-hidden="true"></i></div><p>Sin noticias por ahora.</p></div>
        <?php else: foreach ($moduloNoticias as $n): ?>
          <div class="responsable">
            <span class="doc-flex-grow">
              <span class="nombre"><?= e($n['titulo']) ?></span>
              <span class="cargo"><?= e($n['categoria']) ?> · <?= e($n['fecha']) ?></span>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </section>
    <?php endif; ?>
    <?php if (isset($moduloEventos)): ?>
      <section class="box-card box-card--flat box-card--compact">
        <h2 class="side-box-title">Próximos eventos</h2>
        <?php if (!$moduloEventos): ?>
          <div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-calendar-week" aria-hidden="true"></i></div><p>Sin eventos programados.</p></div>
        <?php else: foreach ($moduloEventos as $ev): ?>
          <div class="responsable">
            <span class="doc-flex-grow">
              <span class="nombre"><?= e($ev['titulo']) ?></span>
              <span class="cargo"><?= e($ev['fecha']) ?> · <?= e($ev['hora_lugar']) ?></span>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </section>
    <?php endif; ?>
    <?php require ROOT_PATH . '/app/Views/Portal/_shared/_modulo_lateral.php'; ?>
  </aside>
</div>

<?php if ($esAdminMod): ?>
<dialog class="modal" id="docNuevo" aria-labelledby="docNuevoT">
  <form action="<?= BASE_URL ?>/documentos/crear" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="tipo" value="direccion">
    <input type="hidden" name="volver" value="<?= e($MR['volver']) ?>">
    <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
    <header class="modal-header">
      <div class="modal-heading">
        <h2 class="modal-title" id="docNuevoT">Agregar documento</h2>
        <p class="modal-desc">Regístralo primero; el archivo se adjunta después desde la tabla.</p>
      </div>
      <button type="button" class="modal-close" data-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="modal-body form-stack">
      <div class="field">
        <label class="field-label" for="dnNombre">Nombre del documento <span class="req" aria-hidden="true">*</span></label>
        <input class="input" type="text" id="dnNombre" name="nombre" required>
      </div>
      <div class="form-grid">
        <div class="field">
          <label class="field-label" for="dnTipo">Tipo <span class="opt">(opcional)</span></label>
          <input class="input" type="text" id="dnTipo" name="tipo_doc" placeholder="Ej. Informe">
        </div>
        <div class="field">
          <label class="field-label" for="dnVersion">Versión <span class="opt">(opcional)</span></label>
          <input class="input" type="text" id="dnVersion" name="version" placeholder="v1.0">
        </div>
        <div class="field">
          <label class="field-label" for="dnFecha">Fecha</label>
          <input class="input" type="date" id="dnFecha" name="fecha" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
    </div>
    <footer class="modal-footer">
      <button type="button" class="btn" data-close>Cancelar</button>
      <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
    </footer>
  </form>
</dialog>
<?php endif; ?>
