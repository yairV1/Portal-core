<?php
/**
 * Mi Google Drive — página propia (antes vivía dentro de Gestión
 * Documental, ver migración 053_gestion_documental_publico.sql). Cada quien
 * conecta SU cuenta y ve/importa SUS propios archivos hacia una carpeta real
 * que administre (direccion_carpetas) — sin relación con lo público/privado
 * del repositorio institucional.
 * Variables que llegan ya resueltas desde DriveUsuarioController.php:
 * @var bool        $miDriveOauthConfigurado
 * @var bool        $miDriveConectado
 * @var array       $misArchivosDrive
 * @var string|null $miDriveSiguientePagina
 * @var array       $carpetasDestinoDrive
 * @var string      $miDriveImportEstado
 * @var int         $miDriveImportTraidos
 * @var array       $miDriveCarpetas
 * @var array       $miDriveArchivosPorCarpeta
 */
$titulo = 'Mi Google Drive';
require ROOT_PATH . '/app/Views/layouts/portal-header.php';
?>
<link rel="stylesheet" href="<?= v('/assets/portal/css/centro-documental.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/portal/css/mi-drive.css') ?>">

<?php ui_page_header([
    'title'   => 'Mi Google Drive',
    'desc'    => 'Conecta tu cuenta personal para ver tus propios archivos y llevarlos a una carpeta real del portal. No tiene relación con el repositorio institucional de Gestión Documental.',
    'actions' => $miDriveOauthConfigurado && $miDriveConectado ? function () use ($csrf) { ?>
        <span class="badge badge-success badge-dot">Conectado</span>
        <form action="<?= BASE_URL ?>/gestion-documental/drive/desconectar" method="post" data-confirm="¿Desconectar tu Google Drive?" data-confirm-text="Podrás volver a conectarlo cuando quieras." data-confirm-ok="Desconectar">
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
          <button type="submit" class="btn btn-sm"><i class="bi bi-x-circle" aria-hidden="true"></i> Desconectar</button>
        </form>
    <?php } : null,
]); ?>

<?php if (!$miDriveOauthConfigurado): ?>
  <div class="alert alert-warning" role="status">
    <i class="bi bi-exclamation-triangle-fill alert-icon" aria-hidden="true"></i>
    <div class="alert-content">
      <p class="alert-title">No disponible en este entorno</p>
      <p class="alert-text">Conectar tu Drive personal necesita tener configurado el inicio de sesión con Google (ver .env.example). Pregúntale al administrador del portal.</p>
    </div>
  </div>

<?php elseif (!$miDriveConectado): ?>
  <?php ui_empty_state([
      'icon'    => 'google',
      'title'   => 'Conecta tu Google Drive',
      'text'    => 'Verás tus propios archivos acá y podrás llevarlos a la carpeta que quieras, sin compartir nada con nadie primero.',
      'actions' => function () { ?><a href="<?= BASE_URL ?>/gestion-documental/drive/conectar" class="btn btn-primary"><i class="bi bi-google" aria-hidden="true"></i> Conectar mi Google Drive</a><?php },
  ]); ?>

<?php else: ?>

  <?php if ($miDriveImportEstado !== 'completo'): ?>
    <div class="alert" id="drive-import-aviso" role="status" aria-live="polite">
      <span class="spinner alert-icon" aria-hidden="true"></span>
      <div class="alert-content">
        <p class="alert-title">Trayendo tu Drive a esta pantalla</p>
        <p class="alert-text"><strong id="drive-import-contador"><?= (int) $miDriveImportTraidos ?></strong> archivo(s) traídos hasta ahora. Puedes seguir usando el portal mientras tanto.</p>
      </div>
    </div>
    <script>
      (function () {
        var contador = document.getElementById('drive-import-contador');
        var aviso = document.getElementById('drive-import-aviso');
        function terminado() {
          aviso.className = 'alert alert-success';
          aviso.querySelector('.alert-icon').outerHTML = '<i class="bi bi-check-circle-fill alert-icon" aria-hidden="true"></i>';
          aviso.querySelector('.alert-title').textContent = 'Listo, ya está todo tu Drive acá';
          var texto = aviso.querySelector('.alert-text');
          texto.textContent = '';
          var enlace = document.createElement('a');
          enlace.href = window.location.pathname;
          enlace.textContent = 'Recargar para verlo';
          texto.appendChild(enlace);
        }
        function siguienteLote() {
          fetch('<?= BASE_URL ?>/gestion-documental/drive/importar-todo/avanzar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=<?= urlencode($csrf) ?>',
          })
            .then(function (r) { return r.json(); })
            .then(function (datos) {
              if (datos.traidos !== undefined) contador.textContent = datos.traidos;
              if (datos.terminado) { terminado(); return; }
              setTimeout(siguienteLote, 1500);
            })
            .catch(function () { setTimeout(siguienteLote, 4000); }); // reintenta más despacio si Drive/la red fallaron un momento
        }
        siguienteLote();
      })();
    </script>
  <?php endif; ?>

  <section>
    <div class="section-header"><h2 class="section-heading"><i class="bi bi-cloud" aria-hidden="true"></i> Archivos en tu Drive</h2></div>
    <?php if (!$misArchivosDrive): ?>
      <?php ui_empty_state(['icon' => 'folder2-open', 'title' => 'No encontramos archivos', 'text' => 'Tu Drive está vacío o Google tardó en responder. Intenta de nuevo en un momento.', 'compact' => true]); ?>
    <?php else: ?>
      <?php if (!$carpetasDestinoDrive): ?>
        <div class="alert" role="status">
          <i class="bi bi-info-circle-fill alert-icon" aria-hidden="true"></i>
          <div class="alert-content"><p class="alert-text">Puedes ver tus archivos, pero no administras ninguna carpeta a la que llevarlos. Pídele a un administrador que te asigne una dirección.</p></div>
        </div>
      <?php endif; ?>
      <div class="doc-archivos">
        <?php foreach ($misArchivosDrive as $af): ?>
          <div class="doc-archivo-row">
            <span class="ic" aria-hidden="true"><i class="bi bi-file-earmark" aria-hidden="true"></i></span>
            <span class="info">
              <span class="nombre"><?= e($af['name'] ?? 'Sin nombre') ?></span>
              <span class="meta"><?= !empty($af['modifiedTime']) ? 'Modificado el ' . e((new DateTime($af['modifiedTime']))->format('d/m/Y')) : '' ?></span>
            </span>
            <span class="doc-actions">
              <?php if (!empty($af['webViewLink'])): ?>
                <a class="btn btn-ghost btn-sm btn-icon" href="<?= e($af['webViewLink']) ?>" target="_blank" rel="noopener" title="Ver en Drive" aria-label="Ver <?= e($af['name'] ?? 'archivo') ?> en Drive"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
              <?php endif; ?>
              <?php if ($carpetasDestinoDrive): ?>
                <form action="<?= BASE_URL ?>/gestion-documental/drive/importar" method="post" class="drive-importar">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="file_id" value="<?= e($af['id']) ?>">
                  <select name="carpeta_id" required class="select input-sm select-inline" aria-label="Carpeta de destino para <?= e($af['name'] ?? 'el archivo') ?>">
                    <option value="">Llevar a…</option>
                    <?php foreach ($carpetasDestinoDrive as $cd): ?>
                      <option value="<?= (int) $cd['id'] ?>"><?= e($cd['label']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-sm"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Llevar</button>
                </form>
              <?php endif; ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($miDriveSiguientePagina): ?>
        <div class="drive-mas"><a href="<?= BASE_URL ?>/mi-drive?drive_token=<?= urlencode($miDriveSiguientePagina) ?>" class="btn">Ver más archivos</a></div>
      <?php endif; ?>
    <?php endif; ?>
  </section>

  <?php if ($miDriveImportEstado === 'completo' && ($miDriveCarpetas || $miDriveArchivosPorCarpeta)): ?>
    <section>
      <div class="section-header section-header--spaced">
        <h2 class="section-heading"><i class="bi bi-folder2" aria-hidden="true"></i> Mi Drive (copia local)</h2>
        <span class="section-desc">Lo que ya trajiste al portal</span>
      </div>
      <?php
        $miDriveHijosDe = [];
        foreach ($miDriveCarpetas as $c) { $miDriveHijosDe[$c['parent_id']][] = $c; }
        $pintarCarpetaDrive = function ($parentId, $nivel) use (&$pintarCarpetaDrive, $miDriveHijosDe, $miDriveArchivosPorCarpeta) {
          foreach ($miDriveHijosDe[$parentId] ?? [] as $c) {
            echo '<div class="drive-nodo drive-nodo--carpeta" style="--nivel:' . (int) $nivel . '"><i class="bi bi-folder2" aria-hidden="true"></i> ' . e($c['nombre']) . '</div>';
            foreach ($miDriveArchivosPorCarpeta[$c['id']] ?? [] as $a) {
              echo '<div class="drive-nodo" style="--nivel:' . (int) ($nivel + 1) . '"><i class="bi bi-file-earmark" aria-hidden="true"></i> <a href="' . BASE_URL . '/gestion-documental/drive/mi-drive/descargar?archivo_id=' . (int) $a['id'] . '">' . e($a['nombre']) . '</a></div>';
            }
            $pintarCarpetaDrive($c['id'], $nivel + 1);
          }
        };
      ?>
      <div class="gd-drive-tree">
        <?php foreach ($miDriveArchivosPorCarpeta[null] ?? [] as $a): ?>
          <div class="drive-nodo" style="--nivel:0"><i class="bi bi-file-earmark" aria-hidden="true"></i> <a href="<?= BASE_URL ?>/gestion-documental/drive/mi-drive/descargar?archivo_id=<?= (int) $a['id'] ?>"><?= e($a['nombre']) ?></a></div>
        <?php endforeach; ?>
        <?php $pintarCarpetaDrive(null, 0); ?>
      </div>
    </section>
  <?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/app/Views/layouts/portal-footer.php'; ?>
