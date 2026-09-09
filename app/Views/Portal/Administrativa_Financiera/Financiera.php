<?php $titulo = 'Administrativa y Financiera'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/administrativa-financiera.css">
<div class="modulo-head">
        <div class="modulo-kicker"><?= e($moduloKicker) ?></div>
        <h1 class="modulo-titulo"><?= e($moduloTitulo) ?></h1>
        <p class="modulo-desc"><?= e($moduloDesc) ?></p>
      </div>

      <div class="modulo-kpis" id="moduloKpis">
        <?php if (!$moduloKpis): ?>
          <p class="text-muted">Sin indicadores por ahora.</p>
        <?php else: foreach ($moduloKpis as $k): ?>
          <div class="modulo-kpi">
            <div class="label"><?= e($k['label']) ?></div>
            <div class="valor"><?= e($k['valor']) ?></div>
            <span class="spark" data-valor="<?= e($k['valor']) ?>"></span>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="modulo-grid">
        <div>
          <div class="section-head"><h4>Áreas del módulo</h4></div>
          <div id="moduloAreas">
            <?php if (!$moduloAreas): ?>
              <p class="text-muted">Sin áreas registradas por ahora.</p>
            <?php else: foreach ($moduloAreas as $a): ?>
              <div class="area-item" id="<?= e(sb_slug($a['label'])) ?>">
                <span class="ic"><i class="bi bi-folder2"></i></span>
                <span style="flex:1">
                  <span class="label"><?= e($a['label']) ?></span>
                  <span class="meta"><?= e($a['meta']) ?></span>
                </span>
              </div>
            <?php endforeach; endif; ?>
          </div>

          <div class="section-head" style="margin-top:40px"><h4>Documentación destacada</h4></div>
          <table class="table">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Actualizado</th><th>Archivo</th></tr></thead>
            <tbody>
              <?php if (!$moduloDocumentos): ?>
                <tr><td colspan="5" class="text-muted">Sin documentos por ahora.</td></tr>
              <?php else: foreach ($moduloDocumentos as $d): ?>
                <tr>
                  <td><strong><?= e($d['nombre']) ?></strong></td>
                  <td style="opacity:.7"><?= e($d['tipo']) ?></td>
                  <td><span class="tag"><?= e($d['version']) ?></span></td>
                  <td style="opacity:.7"><?= e($d['fecha']) ?></td>
                  <td>
                    <?php if ($d['archivo']): ?>
                      <a class="tag tag-accent" href="<?= BASE_URL ?>/documentos/descargar?tipo=direccion&id=<?= (int) $d['id'] ?>">
                        <i class="bi bi-download"></i> Descargar
                      </a>
                    <?php elseif (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
                      <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                        <input type="hidden" name="tipo" value="direccion">
                        <input type="hidden" name="volver" value="/administrativa-financiera">
                        <input type="hidden" name="archivo_id" value="<?= (int) $d['id'] ?>">
                        <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required style="max-width:160px;font-size:11px">
                        <button type="submit" class="tag tag-neutral" style="border:none;cursor:pointer">
                          <i class="bi bi-upload"></i> Subir
                        </button>
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

        <div style="display:flex;flex-direction:column;gap:28px">
          <div class="side-box">
            <div class="side-box-title">Responsables</div>
            <div id="moduloResponsables"></div>
          </div>
          <div class="side-box">
            <div class="side-box-title">Software relacionado</div>
            <div id="moduloSoftware"></div>
          </div>
        </div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/administrativa-financiera.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
