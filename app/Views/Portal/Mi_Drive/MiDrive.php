<?php
/**
 * Mi Google Drive — página propia (antes vivía dentro de Gestión
 * Documental, ver migración 053_gestion_documental_publico.sql: esa vista
 * pasó a ser solo el repositorio institucional público). Cada quien
 * conecta SU cuenta y ve/importa SUS propios archivos hacia una carpeta
 * real que administre (direccion_carpetas) — sin relación con lo
 * público/privado del repositorio institucional.
 * Variables que llegan ya resueltas desde DriveUsuarioController.php (vía
 * require, mismo scope):
 * @var bool        $miDriveOauthConfigurado
 * @var bool        $miDriveConectado
 * @var array        $misArchivosDrive
 * @var string|null  $miDriveSiguientePagina
 * @var array        $carpetasDestinoDrive
 * @var string       $miDriveImportEstado
 * @var int          $miDriveImportTraidos
 * @var array        $miDriveCarpetas
 * @var array        $miDriveArchivosPorCarpeta
 */
$titulo = 'Mi Google Drive';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/mi-drive.css') ?>">

<h1 class="page-title"><i class="bi bi-google"></i> Mi Google Drive</h1>
<p class="page-desc">Conecta tu cuenta personal para ver tus propios archivos acá y llevarlos a una carpeta real del portal — no tiene relación con el repositorio institucional de Gestión Documental.</p>

<?php if (!$miDriveOauthConfigurado): ?>
  <p class="widget-empty"><i class="bi bi-google"></i> Conectar tu Drive personal necesita tener configurado el login con Google (ver .env.example) — pregúntale al administrador del portal.</p>
<?php elseif (!$miDriveConectado): ?>
  <p class="text-muted" style="margin:0 0 10px">Conecta tu cuenta para ver tus propios archivos de Drive acá y llevarlos a la carpeta que quieras — sin compartir nada con nadie primero.</p>
  <a href="<?= BASE_URL ?>/gestion-documental/drive/conectar" class="doc-btn doc-btn--primary" style="text-decoration:none;display:inline-flex">
    <i class="bi bi-google"></i> Conectar mi Google Drive
  </a>
<?php else: ?>
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
    <span class="text-muted"><i class="bi bi-check-circle-fill" style="color:var(--color-success)"></i> Tu Google Drive está conectado.</span>
    <form action="<?= BASE_URL ?>/gestion-documental/drive/desconectar" method="post" onsubmit="return confirm('¿Desconectar tu Google Drive? Podrás volver a conectarlo cuando quieras.')">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <button type="submit" class="doc-btn doc-btn--secondary"><i class="bi bi-x-circle"></i> Desconectar</button>
    </form>
  </div>

  <?php if (!$misArchivosDrive): ?>
    <p class="widget-empty"><i class="bi bi-folder2-open"></i> No encontramos archivos en tu Drive — o Google tardó en responder, intenta de nuevo en un momento.</p>
  <?php elseif (!$carpetasDestinoDrive): ?>
    <p class="widget-empty"><i class="bi bi-lock"></i> Puedes ver tus archivos, pero no administras ninguna carpeta a la que llevarlos — pídele a un administrador que te asigne una dirección.</p>
  <?php else: ?>
    <div class="doc-archivos" style="margin-bottom:10px">
      <?php foreach ($misArchivosDrive as $af): ?>
        <div class="doc-archivo-row">
          <span class="ic"><i class="bi bi-file-earmark"></i></span>
          <span class="info">
            <span class="nombre"><?= e($af['name'] ?? 'Sin nombre') ?></span>
            <span class="meta"><?= !empty($af['modifiedTime']) ? e((new DateTime($af['modifiedTime']))->format('d/m/Y')) : '' ?></span>
          </span>
          <span class="doc-actions">
            <?php if (!empty($af['webViewLink'])): ?>
              <a class="doc-btn doc-btn--secondary doc-btn--icon" href="<?= e($af['webViewLink']) ?>" target="_blank" rel="noopener" title="Ver en Drive"><i class="bi bi-eye"></i></a>
            <?php endif; ?>
            <form action="<?= BASE_URL ?>/gestion-documental/drive/importar" method="post" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="file_id" value="<?= e($af['id']) ?>">
              <select name="carpeta_id" required class="doc-select" style="max-width:200px">
                <option value="">Llevar a...</option>
                <?php foreach ($carpetasDestinoDrive as $cd): ?>
                  <option value="<?= (int) $cd['id'] ?>"><?= e($cd['label']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="doc-btn doc-btn--secondary doc-btn--icon" title="Llevar a la carpeta elegida"><i class="bi bi-box-arrow-in-down"></i></button>
            </form>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($miDriveSiguientePagina): ?>
      <a href="<?= BASE_URL ?>/mi-drive?drive_token=<?= urlencode($miDriveSiguientePagina) ?>" class="doc-btn doc-btn--secondary" style="text-decoration:none;display:inline-flex">Ver más archivos</a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($miDriveImportEstado !== 'completo'): ?>
    <p id="drive-import-aviso" class="widget-empty" style="margin-top:14px">
      <i class="bi bi-cloud-arrow-down"></i>
      Trayendo tu Drive a esta pantalla, con calma — <strong id="drive-import-contador"><?= (int) $miDriveImportTraidos ?></strong> archivo(s) traídos hasta ahora.
    </p>
    <script>
      (function () {
        var contador = document.getElementById('drive-import-contador');
        var aviso = document.getElementById('drive-import-aviso');
        function siguienteLote() {
          fetch('<?= BASE_URL ?>/gestion-documental/drive/importar-todo/avanzar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=<?= urlencode($csrf) ?>',
          })
            .then(function (r) { return r.json(); })
            .then(function (datos) {
              if (datos.traidos !== undefined) contador.textContent = datos.traidos;
              if (datos.terminado) {
                aviso.innerHTML = '<i class="bi bi-check-circle-fill" style="color:var(--color-success)"></i> Listo, ya está todo tu Drive acá. <a href="' + window.location.pathname + '">Recargar para verlo</a>';
                return;
              }
              setTimeout(siguienteLote, 1500);
            })
            .catch(function () { setTimeout(siguienteLote, 4000); }); // reintenta más despacio si Drive/la red fallaron un momento
        }
        siguienteLote();
      })();
    </script>
  <?php elseif ($miDriveCarpetas || $miDriveArchivosPorCarpeta): ?>
    <div class="doc-section-head" style="margin:18px 0 10px"><h4><i class="bi bi-folder2"></i> Mi Drive (copia local)</h4></div>
    <?php
      $miDriveHijosDe = [];
      foreach ($miDriveCarpetas as $c) { $miDriveHijosDe[$c['parent_id']][] = $c; }
      $pintarCarpetaDrive = function ($parentId, $nivel) use (&$pintarCarpetaDrive, $miDriveHijosDe, $miDriveArchivosPorCarpeta) {
        foreach ($miDriveHijosDe[$parentId] ?? [] as $c) {
          echo '<div style="margin:4px 0 4px ' . ($nivel * 18) . 'px"><i class="bi bi-folder2"></i> ' . e($c['nombre']) . '</div>';
          foreach ($miDriveArchivosPorCarpeta[$c['id']] ?? [] as $a) {
            echo '<div style="margin:2px 0 2px ' . (($nivel + 1) * 18) . 'px"><i class="bi bi-file-earmark"></i> <a href="' . BASE_URL . '/gestion-documental/drive/mi-drive/descargar?archivo_id=' . (int) $a['id'] . '">' . e($a['nombre']) . '</a></div>';
          }
          $pintarCarpetaDrive($c['id'], $nivel + 1);
        }
      };
    ?>
    <div class="gd-drive-tree">
      <?php foreach ($miDriveArchivosPorCarpeta[null] ?? [] as $a): ?>
        <div><i class="bi bi-file-earmark"></i> <a href="<?= BASE_URL ?>/gestion-documental/drive/mi-drive/descargar?archivo_id=<?= (int) $a['id'] ?>"><?= e($a['nombre']) ?></a></div>
      <?php endforeach; ?>
      <?php $pintarCarpetaDrive(null, 0); ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
