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
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  window.usuarioNombre = <?= json_encode(!empty($nombre) && $nombre !== 'Invitado' ? explode(' ', trim($nombre))[0] : '') ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      title: <?= json_encode('¡Bienvenido' . (!empty($nombre) && $nombre !== 'Invitado' ? ', ' . explode(' ', trim($nombre))[0] : '') . '!') ?>,
      text: 'Core está lista para ayudarte a encontrar lo que necesites.',
      timer: 3200,
      timerProgressBar: true,
      showConfirmButton: false
    });
  });
</script>
<?php endif; ?>

<script src="<?= v('/assets/layouts/js/paneles.js') ?>"></script>
<script src="<?= v('/assets/layouts/js/asistente.js') ?>"></script>
</body>
</html>
