<?php $titulo = 'Gestión Documental'; require ROOT_PATH . '/app/Views/layouts/portal-header.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/portal/css/gestion-documental.css">
<link rel="stylesheet" href="<?= v('/assets/portal/css/modulo-generico.css') ?>">
<h1 class="page-title">Gestión Documental</h1>
<p class="page-desc">Repositorio institucional de documentos, formatos y control de versiones.</p>

<?php $ESTADO_TAG = ['Vigente' => 'success', 'En revisión' => 'warning', 'Obsoleto' => 'danger']; ?>

<div class="section-head" style="margin-bottom:14px"><h4><i class="bi bi-google"></i> Mi Google Drive</h4></div>
<?php if (!$miDriveOauthConfigurado): ?>
  <div class="modulo-vacio"><i class="bi bi-google"></i> Conectar tu Drive personal necesita tener configurado el login con Google (ver .env.example) — pregúntale al administrador del portal.</div>
<?php elseif (!$miDriveConectado): ?>
  <p class="text-muted" style="margin:0 0 10px">Conecta tu cuenta para ver tus propios archivos de Drive acá y llevarlos a la carpeta que quieras — sin compartir nada con nadie primero.</p>
  <a href="<?= BASE_URL ?>/gestion-documental/drive/conectar" class="tag tag-accent" style="border:none;cursor:pointer;display:inline-flex;text-decoration:none">
    <i class="bi bi-google"></i>&nbsp;Conectar mi Google Drive
  </a>
<?php else: ?>
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
    <span class="text-muted"><i class="bi bi-check-circle-fill" style="color:var(--color-success)"></i> Tu Google Drive está conectado.</span>
    <form action="<?= BASE_URL ?>/gestion-documental/drive/desconectar" method="post" onsubmit="return confirm('¿Desconectar tu Google Drive? Podrás volver a conectarlo cuando quieras.')">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <button type="submit" class="tag tag-neutral" style="border:none;cursor:pointer"><i class="bi bi-x-circle"></i> Desconectar</button>
    </form>
  </div>

  <?php if (!$misArchivosDrive): ?>
    <div class="modulo-vacio"><i class="bi bi-folder2-open"></i> No encontramos archivos en tu Drive — o Google tardó en responder, intenta de nuevo en un momento.</div>
  <?php elseif (!$carpetasDestinoDrive): ?>
    <div class="modulo-vacio"><i class="bi bi-lock"></i> Puedes ver tus archivos, pero no administras ninguna carpeta a la que llevarlos — pídele a un administrador que te asigne una dirección para poder organizar documentos ahí.</div>
  <?php else: ?>
    <div style="overflow-x:auto">
    <table class="table" style="margin-bottom:10px">
      <thead><tr><th>Archivo</th><th>Modificado</th><th></th><th>Llevar a...</th></tr></thead>
      <tbody>
        <?php foreach ($misArchivosDrive as $af): ?>
          <tr>
            <td><i class="bi bi-file-earmark"></i> <?= e($af['name'] ?? 'Sin nombre') ?></td>
            <td style="opacity:.7;white-space:nowrap"><?= !empty($af['modifiedTime']) ? (new DateTime($af['modifiedTime']))->format('d/m/Y') : '' ?></td>
            <td>
              <?php if (!empty($af['webViewLink'])): ?>
                <a class="tag tag-neutral" href="<?= e($af['webViewLink']) ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Ver</a>
              <?php endif; ?>
            </td>
            <td>
              <form action="<?= BASE_URL ?>/gestion-documental/drive/importar" method="post" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="file_id" value="<?= e($af['id']) ?>">
                <select name="carpeta_id" required style="max-width:260px;padding:6px 8px;border-radius:8px;border:1px solid var(--color-divider);background:var(--color-bg);color:var(--color-text)">
                  <option value="">Elige una carpeta...</option>
                  <?php foreach ($carpetasDestinoDrive as $cd): ?>
                    <option value="<?= (int) $cd['id'] ?>"><?= e($cd['label']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="tag tag-accent" style="border:none;cursor:pointer"><i class="bi bi-box-arrow-in-down"></i> Traer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php if ($miDriveSiguientePagina): ?>
      <a href="<?= BASE_URL ?>/gestion-documental?drive_token=<?= urlencode($miDriveSiguientePagina) ?>#" class="tag tag-neutral" style="border:none;text-decoration:none">Ver más archivos</a>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>

<div class="section-head" style="margin:26px 0 14px"><h4>Repositorio institucional</h4></div>
<?php if (!$direccionesDoc): ?>
  <div class="empty-state">
    <div class="ic"><i class="bi bi-cone-striped"></i></div>
    <h4>Módulo en construcción</h4>
    <p>Este módulo todavía no tiene contenido cargado. Vuelve pronto.</p>
  </div>
<?php else: foreach ($direccionesDoc as $i => $dir): ?>
  <div class="<?= $i === 0 ? '' : 'subsection' ?>">
    <h4 class="section-title"><?= e($dir['label']) ?></h4>

    <?php foreach ($areasPorDireccion[$dir['id']] as $area): ?>
      <p class="text-muted" style="margin:18px 0 8px"><?= e($area['label']) ?></p>
      <table class="table" style="margin-bottom:24px">
        <thead><tr><th>Documento</th><th>Tipo</th><th>Ver.</th><th>Responsable</th><th>Actualizado</th><th>Archivo</th></tr></thead>
        <tbody>
          <?php if (!$archivosPorCarpeta[$area['id']]): ?>
            <tr><td colspan="6"><div class="modulo-vacio"><i class="bi bi-file-earmark-text"></i> Sin documentos por ahora.</div></td></tr>
          <?php endif; ?>
          <?php foreach ($archivosPorCarpeta[$area['id']] as $arc): ?>
            <tr>
              <td><strong><?= e($arc['nombre']) ?></strong></td>
              <td style="opacity:.7"><?= e($arc['tipo']) ?></td>
              <td>
                <span style="display:inline-flex;align-items:center;gap:8px">
                  <span class="tag-stamp"><?= e($arc['version']) ?></span>
                  <span class="tag tag-<?= e($ESTADO_TAG[$arc['estado']] ?? 'info') ?>"><?= e($arc['estado']) ?></span>
                </span>
              </td>
              <td style="opacity:.7"><?= e($arc['responsable']) ?></td>
              <td style="opacity:.7"><?= $arc['fecha'] ? (new DateTime($arc['fecha']))->format('d/m/Y') : '' ?></td>
              <td>
                <?php if ($arc['archivo']): ?>
                  <a class="tag tag-accent" href="<?= BASE_URL ?>/documentos/descargar?tipo=documental&id=<?= (int) $arc['id'] ?>">
                    <i class="bi bi-download"></i> Descargar
                  </a>
                <?php elseif (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
                  <form action="<?= BASE_URL ?>/documentos/subir" method="post" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="tipo" value="documental">
                    <input type="hidden" name="volver" value="/gestion-documental">
                    <input type="hidden" name="archivo_id" value="<?= (int) $arc['id'] ?>">
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
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
        <form action="<?= BASE_URL ?>/documentos/crear" method="post" class="modulo-add" style="margin-top:-8px">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <input type="hidden" name="tipo" value="documental">
          <input type="hidden" name="volver" value="/gestion-documental">
          <input type="hidden" name="carpeta_id" value="<?= (int) $area['id'] ?>">
          <input type="text" name="nombre" placeholder="Nombre del documento" required>
          <input type="text" name="tipo_doc" placeholder="Tipo (ej: Formato)">
          <input type="text" name="version" placeholder="v1.0">
          <input type="text" name="responsable" placeholder="Responsable">
          <input type="date" name="fecha" value="<?= date('Y-m-d') ?>">
          <button type="submit" class="tag tag-accent" style="border:none;cursor:pointer"><i class="bi bi-plus-lg"></i> Agregar documento</button>
        </form>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endforeach; endif; ?>
<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
