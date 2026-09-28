<?php
/**
 * Estado de "Mi Google Drive" en el Inicio (usuario y admin — antes estaba
 * copiado en las dos vistas). Recibe $miDriveOauthConfigurado y
 * $miDriveConectado (HomeController.php).
 */
?>
<?php if (!$miDriveOauthConfigurado): ?>
  <div class="empty-state empty-state--compact"><div class="ic" aria-hidden="true"><i class="bi bi-google" aria-hidden="true"></i></div><p>No configurado en este entorno.</p></div>
<?php else: ?>
  <div class="drive-widget">
    <?php if ($miDriveConectado): ?>
      <span class="badge badge-success badge-dot">Conectado</span>
      <p class="drive-widget-texto">Tus archivos de Drive ya se pueden traer al portal.</p>
      <a href="<?= BASE_URL ?>/mi-drive" class="btn btn-sm"><i class="bi bi-folder2" aria-hidden="true"></i> Ver mis archivos</a>
    <?php else: ?>
      <p class="drive-widget-texto">Trae tus propios archivos de Drive al portal.</p>
      <a href="<?= BASE_URL ?>/mi-drive" class="btn btn-sm btn-primary"><i class="bi bi-google" aria-hidden="true"></i> Conectar</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
