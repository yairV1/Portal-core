<?php $titulo = 'Novedades'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<div class="modulo-head">
        <div class="modulo-kicker"><?= e($moduloKicker) ?></div>
        <h1 class="modulo-titulo"><?= e($moduloTitulo) ?></h1>
        <p class="modulo-desc"><?= e($moduloDesc) ?></p>
      </div>

      <div class="modulo-kpis" id="moduloKpis">
        <?php if (!$moduloKpis): ?>
          <div class="modulo-vacio"><i class="bi bi-bar-chart-line"></i> Sin indicadores por ahora.</div>
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
              <div class="modulo-vacio"><i class="bi bi-folder2"></i> Sin áreas registradas por ahora.</div>
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

          <div class="section-head" style="margin-top:40px"><h4>Documentación destacada</h4></div>
          <table class="table">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Actualizado</th><th>Archivo</th></tr></thead>
            <tbody>
              <?php if (!$moduloDocumentos): ?>
                <tr><td colspan="5"><div class="modulo-vacio"><i class="bi bi-file-earmark-text"></i> Sin documentos por ahora.</div></td></tr>
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
                        <input type="hidden" name="volver" value="/novedades">
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
          <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin' && $direccion): ?>
            <form action="<?= BASE_URL ?>/documentos/crear" method="post" class="modulo-add">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="tipo" value="direccion">
              <input type="hidden" name="volver" value="/novedades">
              <input type="hidden" name="direccion_id" value="<?= (int) $direccion['id'] ?>">
              <input type="text" name="nombre" placeholder="Nombre del documento" required>
              <input type="text" name="tipo_doc" placeholder="Tipo">
              <input type="text" name="version" placeholder="v1.0">
              <input type="date" name="fecha" value="<?= date('Y-m-d') ?>">
              <button type="submit" class="tag tag-accent" style="border:none;cursor:pointer"><i class="bi bi-plus-lg"></i> Agregar documento</button>
            </form>
          <?php endif; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:28px">
          <div class="side-box">
            <div class="side-box-title">Últimas noticias</div>
            <?php if (!$moduloNoticias): ?>
              <p class="text-muted">Sin noticias por ahora.</p>
            <?php else: foreach ($moduloNoticias as $n): ?>
              <div class="responsable">
                <span style="flex:1">
                  <span class="nombre"><?= e($n['titulo']) ?></span>
                  <span class="cargo"><?= e($n['categoria']) ?> · <?= e($n['fecha']) ?></span>
                </span>
              </div>
            <?php endforeach; endif; ?>
          </div>
          <div class="side-box">
            <div class="side-box-title">Próximos eventos</div>
            <?php if (!$moduloEventos): ?>
              <p class="text-muted">Sin eventos programados.</p>
            <?php else: foreach ($moduloEventos as $ev): ?>
              <div class="responsable">
                <span style="flex:1">
                  <span class="nombre"><?= e($ev['titulo']) ?></span>
                  <span class="cargo"><?= e($ev['fecha']) ?> · <?= e($ev['hora_lugar']) ?></span>
                </span>
              </div>
            <?php endforeach; endif; ?>
          </div>
          <div class="side-box">
            <div class="side-box-title">Responsables</div>
            <div id="moduloResponsables">
              <?php if (!$moduloResponsables): ?>
                <div class="modulo-vacio"><i class="bi bi-people"></i> Sin responsables por ahora.</div>
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
          </div>
          <div class="side-box">
            <div class="side-box-title">Software relacionado</div>
            <div id="moduloSoftware">
              <?php if (!$moduloSoftware): ?>
                <div class="modulo-vacio"><i class="bi bi-link-45deg"></i> Sin software relacionado.</div>
              <?php else: foreach ($moduloSoftware as $s): ?>
                <div class="software-item"><span style="opacity:.6"><i class="bi bi-link-45deg"></i></span><?= e($s['nombre']) ?></div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>
<script src="<?= BASE_URL ?>/assets/portal/js/novedades.js"></script>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
