<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Tema por defecto de SweetAlert2 con los colores institucionales.
  if (typeof Swal !== 'undefined') {
    window.SwalBrand = Swal.mixin({
      confirmButtonColor: '#9E1F63',
      cancelButtonColor: '#8b8496',
      buttonsStyling: true,
      customClass: { popup: 'rounded-4' }
    });
  }
</script>
</body>
</html>