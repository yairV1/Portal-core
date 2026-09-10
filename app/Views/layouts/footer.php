<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>
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