(function () {
  var cargoInput = document.getElementById('cargoAplicado');
  var vacanteIdInput = document.getElementById('vacanteId');
  if (!cargoInput || !vacanteIdInput) return;

  document.querySelectorAll('.btn-postular').forEach(function (btn) {
    btn.addEventListener('click', function () {
      cargoInput.value = btn.dataset.titulo || '';
      vacanteIdInput.value = btn.dataset.id || '';
      document.getElementById('postular').scrollIntoView({ behavior: 'smooth', block: 'start' });
      cargoInput.focus({ preventScroll: true });
    });
  });
})();
