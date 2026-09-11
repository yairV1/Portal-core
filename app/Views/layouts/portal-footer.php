</div><!-- /.content -->
</div><!-- /.app-shell -->

<!-- ── Asistente flotante "Core" (ver asistente.js) ── -->
<button class="asistente-fab" id="btnAsistente" title="Asistente del portal">
  <img src="<?= BASE_URL ?>/uploads/mascota/core-avatar.png" alt="Core">
  <span class="badge-nuevo" id="asistenteBadge"></span>
</button>
<div class="asistente-panel" id="asistentePanel" hidden>
  <div class="asistente-header">
    <span class="asistente-avatar"><img src="<?= BASE_URL ?>/uploads/mascota/core-avatar.png" alt="Core"></span>
    <div>
      <strong>Core</strong>
      <small>Asistente del Portal</small>
    </div>
    <button type="button" class="asistente-close" id="asistenteClose" title="Cerrar">&times;</button>
  </div>
  <div class="asistente-mensajes" id="asistenteMensajes"></div>
  <form class="asistente-form" id="asistenteForm">
    <input type="text" id="asistenteInput" placeholder="¿Qué estás buscando?" autocomplete="off">
    <button type="submit" title="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
<script>
  window.BASE_URL = <?= json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  window.usuarioNombre = <?= json_encode(!empty($nombre) && $nombre !== 'Invitado' ? explode(' ', trim($nombre))[0] : '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>
<script>
  // Mismo tema institucional de SweetAlert2 que usa la pantalla de login
  // (ver footer.php) — acá hace falta para el diálogo de "cerrar sesión".
  if (typeof Swal !== 'undefined') {
    window.SwalBrand = Swal.mixin({
      confirmButtonColor: '#9E1F63',
      cancelButtonColor: '#8b8496',
      buttonsStyling: true,
      customClass: { popup: 'rounded-4' }
    });
  }
</script>

<?php if (!empty($_GET['bienvenida'])): ?>
<script>
  // Aviso de bienvenida al iniciar sesión (AuthController agrega
  // ?bienvenida=1 al redirigir a "/" tras un login correcto).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    // toast: true → aviso pequeño en la esquina, sin fondo oscurecido
    // tapando el dashboard detrás.
    SwalBrand.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      iconHtml: '<img src="<?= BASE_URL ?>/uploads/mascota/core-avatar.png" alt="Core" style="width:100%;height:100%;object-fit:cover;border-radius:50%">',
      title: <?= json_encode('¡Bienvenido' . (!empty($nombre) && $nombre !== 'Invitado' ? ', ' . explode(' ', trim($nombre))[0] : '') . '!', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      text: 'Core está lista para ayudarte a encontrar lo que necesites.',
      timer: 3200,
      timerProgressBar: true,
      showConfirmButton: false
    });
  });
</script>
<?php endif; ?>

<?php if (isset($_GET['perfil'])): ?>
<script>
  // Aviso tras guardar el perfil (ver PerfilController.php, que agrega
  // ?perfil=1|error|formato|tamano al volver a "/").
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    var resultado = <?= json_encode($_GET['perfil'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var textos = {
      '1': { icon: 'success', title: 'Perfil actualizado' },
      'formato': { icon: 'error', title: 'La foto debe ser JPG, PNG o WEBP' },
      'tamano': { icon: 'error', title: 'La foto pesa más de 2 MB' },
      'error': { icon: 'error', title: 'No se pudo guardar el perfil' }
    };
    var t = textos[resultado] || textos['error'];
    SwalBrand.fire({ toast: true, position: 'top-end', icon: t.icon, title: t.title, timer: 3000, timerProgressBar: true, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<?php if (isset($_GET['doc'])): ?>
<script>
  // Aviso tras subir un documento (ver DocumentoController.php, que agrega
  // ?doc=1|error|formato|tamano al volver a gestion-documental).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    var resultado = <?= json_encode($_GET['doc'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var textos = {
      '1': { icon: 'success', title: 'Documento subido correctamente' },
      'formato': { icon: 'error', title: 'El archivo debe ser PDF, Word, Excel o PowerPoint' },
      'tamano': { icon: 'error', title: 'El archivo pesa más de 20 MB' },
      'error': { icon: 'error', title: 'No se pudo subir el documento' }
    };
    var t = textos[resultado] || textos['error'];
    SwalBrand.fire({ toast: true, position: 'top-end', icon: t.icon, title: t.title, timer: 3000, timerProgressBar: true, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<?php if (isset($_GET['drive'])): ?>
<script>
  // Aviso tras crear/subir/eliminar en el explorador de documentos (ver
  // CarpetaController.php, que agrega ?drive=... al volver al módulo).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    var resultado = <?= json_encode($_GET['drive'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var textos = {
      '1': { icon: 'success', title: 'Documento subido correctamente' },
      'carpeta': { icon: 'success', title: 'Carpeta creada' },
      'eliminado': { icon: 'success', title: 'Documento eliminado' },
      'carpeta_eliminada': { icon: 'success', title: 'Carpeta eliminada' },
      'nombre': { icon: 'error', title: 'Escribe un nombre de carpeta válido' },
      'formato': { icon: 'error', title: 'El archivo debe ser PDF, Word, Excel, PowerPoint o una imagen (JPG/PNG/WEBP)' },
      'tamano': { icon: 'error', title: 'El archivo pesa más de 15 MB' },
      'error': { icon: 'error', title: 'No se pudo completar la acción' }
    };
    var t = textos[resultado] || textos['error'];
    SwalBrand.fire({ toast: true, position: 'top-end', icon: t.icon, title: t.title, timer: 3000, timerProgressBar: true, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<?php if (isset($_GET['evento'])): ?>
<script>
  // Aviso tras crear un evento (ver EventoController.php, que agrega
  // ?evento=1|error al volver a calendario).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    var resultado = <?= json_encode($_GET['evento'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var textos = {
      '1': { icon: 'success', title: 'Evento creado correctamente' },
      'editado': { icon: 'success', title: 'Evento actualizado correctamente' },
      'eliminado': { icon: 'success', title: 'Evento eliminado' },
      'error': { icon: 'error', title: 'No se pudo guardar el evento' }
    };
    var t = textos[resultado] || textos['error'];
    SwalBrand.fire({ toast: true, position: 'top-end', icon: t.icon, title: t.title, timer: 3000, timerProgressBar: true, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<?php if (isset($_GET['pendiente'])): ?>
<script>
  // Aviso tras crear/eliminar un pendiente (ver PendienteController.php,
  // que agrega ?pendiente=1|eliminado|error al volver a Inicio).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    var resultado = <?= json_encode($_GET['pendiente'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var textos = {
      '1': { icon: 'success', title: 'Pendiente agregado' },
      'eliminado': { icon: 'success', title: 'Pendiente eliminado' },
      'error': { icon: 'error', title: 'No se pudo guardar el pendiente' }
    };
    var t = textos[resultado] || textos['error'];
    SwalBrand.fire({ toast: true, position: 'top-end', icon: t.icon, title: t.title, timer: 3000, timerProgressBar: true, showConfirmButton: false });
  });
</script>
<?php endif; ?>

<script src="<?= v('/assets/layouts/js/paneles.js') ?>"></script>
<script src="<?= v('/assets/layouts/js/asistente.js') ?>"></script>
</body>
</html>
