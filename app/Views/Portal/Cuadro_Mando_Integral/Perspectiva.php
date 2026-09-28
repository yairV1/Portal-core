<?php require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<div class="modulo-head">
  <div class="modulo-kicker"><?= e($moduloKicker) ?></div>
  <h1 class="modulo-titulo"><?= e($moduloTitulo) ?></h1>
  <p class="modulo-desc"><?= e($moduloDesc) ?></p>
</div>

<div class="modulo-kpis" id="moduloKpis">
  <?php if (!$moduloKpis): ?>
    <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></div><p>Sin indicadores por ahora.</p></div>
  <?php else: foreach ($moduloKpis as $k): ?>
    <div class="modulo-kpi">
      <div class="label"><?= e($k['label']) ?></div>
      <div class="valor"><?= e($k['valor']) ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="modulo-grid">
  <div>
    <div class="section-header"><h2 class="section-heading">Áreas de esta perspectiva</h2></div>
    <div id="moduloAreas">
      <?php if (!$moduloAreas): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-folder2"></i></div><p>Sin áreas registradas por ahora.</p></div>
      <?php else: foreach ($moduloAreas as $a): ?>
        <div class="area-item">
          <span class="ic"><i class="bi bi-folder2"></i></span>
          <span style="flex:1">
            <span class="label"><?= e($a['label']) ?></span>
            <span class="meta"><?= e($a['meta']) ?></span>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="section-header section-header--spaced">
            <h2 class="section-heading">Documentación destacada</h2>
            <?php if ($direccion && usuario_admin_de($direccion['id'])): ?>
              <button type="button" class="btn btn-sm btn-primary" data-open="docNuevo" aria-haspopup="dialog"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar documento</button>
            <?php endif; ?>
          </div>
    <div class="table-wrap">
  <table class="table table--stack">
      <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Actualizado</th><th>Archivo</th></tr></thead>
      <tbody>
        <?php if (!$moduloDocumentos): ?>
          <tr><td colspan="5"><div class="empty-state empty-state--compact empty-state--bare"><div class="ic" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></div><p>Sin documentos por ahora.</p></div></td></tr>
        <?php else: foreach ($moduloDocumentos as $d): ?>
          <tr>
            <td><strong><?= e($d['nombre']) ?></strong></td>
            <td class="cell-muted"><?= e($d['tipo']) ?></td>
            <td><span class="badge"><?= e($d['version']) ?></span></td>
            <td class="cell-muted"><?= e($d['fecha']) ?></td>
            <td>
              <?php if ($d['archivo']): ?>
                <a class="btn btn-sm" href="<?= BASE_URL ?>/documentos/descargar?tipo=direccion&id=<?= (int) $d['id'] ?>">
                  <i class="bi bi-download"></i> Descargar
                </a>
              <?php elseif (usuario_admin_de($direccion['id'] ?? null)): ?>
                <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="tipo" value="direccion">
                  <input type="hidden" name="volver" value="<?= e($uri) ?>">
                  <input type="hidden" name="archivo_id" value="<?= (int) $d['id'] ?>">
                        <label class="file-btn btn btn-sm"><i class="bi bi-upload" aria-hidden="true"></i> Subir archivo
                          <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required onchange="this.form.submit()" aria-label="Subir archivo para <?= e($d['nombre']) ?>">
                        </label>
                </form>
              <?php else: ?>
                <span class="text-muted">Sin archivo</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:28px">
    <div class="side-box">
      <div class="side-box-title">Responsables</div>
      <?php if (!$moduloResponsables): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-people"></i></div><p>Sin responsables por ahora.</p></div>
      <?php else: foreach ($moduloResponsables as $r):
          $partesNombre = preg_split('/\s+/', trim($r['nombre']));
          $iniciales = mb_strtoupper(mb_substr($partesNombre[0], 0, 1) . mb_substr(end($partesNombre), 0, 1));
      ?>
        <div class="responsable">
          <span class="ini"><?= e($iniciales) ?></span>
          <span style="flex:1">
            <span class="nombre"><?= e($r['nombre']) ?></span>
            <span class="cargo"><?= e($r['cargo']) ?></span>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <div class="side-box">
      <div class="side-box-title">Software relacionado</div>
      <?php if (!$moduloSoftware): ?>
        <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-link-45deg"></i></div><p>Sin software relacionado.</p></div>
      <?php else: foreach ($moduloSoftware as $s): ?>
        <div class="software-item"><span style="opacity:.6"><i class="bi bi-link-45deg"></i></span><?= e($s['nombre']) ?></div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php if ($direccion && usuario_admin_de($direccion['id'])): ?>
<dialog class="modal" id="docNuevo" aria-labelledby="docNuevoT">
  <form action="<?= BASE_URL ?>/documentos/crear" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="tipo" value="direccion">
    <input type="hidden" name="volver" value="<?= e($uri) ?>">
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
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
