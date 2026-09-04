</div><!-- /.content -->
</div><!-- /.app-shell -->

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
      title: <?= json_encode('¡Bienvenido' . (!empty($nombre) && $nombre !== 'Invitado' ? ', ' . explode(' ', trim($nombre))[0] : '') . '!') ?>,
      text: 'Iniciaste sesión correctamente.',
      timer: 3200,
      timerProgressBar: true,
      showConfirmButton: false
    });
  });
</script>
<?php endif; ?>

<script src="<?= BASE_URL ?>/assets/layouts/js/paneles.js"></script>
</body>
</html>
