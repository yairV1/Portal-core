// ============================================================
// Sistema de Control de Salones AC — COREDUCACIÓN
// JS general del panel: SweetAlert2 (mensajes flash + confirmaciones),
// toggle de la barra lateral y helpers usados por las vistas admin.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  const params = new URLSearchParams(window.location.search);

  if (typeof Swal === 'undefined') return;

  const toast = (icon, title) => (window.SwalBrand || Swal).fire({
    icon, title, timer: 2200, showConfirmButton: false, toast: true,
    position: 'top-end',
  });

  if (params.has('ok')) toast('success', 'Registro guardado correctamente');
  if (params.has('pin_ok')) toast('success', 'PIN actualizado');
  if (params.has('qr_ok')) toast('success', 'QR regenerado correctamente');
  if (params.has('cerrado')) toast('success', 'Registro cerrado correctamente');
  if (params.get('pin_reenviado') === '1') toast('success', 'PIN reenviado al correo del docente');
  if (params.get('pin_reenviado') === '0') toast('error', 'No se pudo reenviar el PIN');

  // ---- Confirmaciones con SweetAlert2 para formularios/botones marcados
  //      con la clase .js-confirm y data-confirm-text="..." ----
  document.querySelectorAll('.js-confirm').forEach(function (el) {
    el.addEventListener('click', function (ev) {
      ev.preventDefault();
      const texto = el.dataset.confirmText || '¿Confirmar esta acción?';
      const form = el.closest('form');
      (window.SwalBrand || Swal).fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        text: texto,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
      }).then(function (result) {
        if (result.isConfirmed) {
          if (form) form.submit();
          else if (el.dataset.href) window.location.href = el.dataset.href;
        }
      });
    });
  });

  // ---- Filtro instantáneo de tablas: <input data-table-filter="#idTabla"> ----
  document.querySelectorAll('[data-table-filter]').forEach(function (input) {
    const tabla = document.querySelector(input.dataset.tableFilter);
    if (!tabla) return;
    input.addEventListener('input', function () {
      const q = input.value.trim().toLowerCase();
      tabla.querySelectorAll('tbody tr').forEach(function (fila) {
        fila.style.display = fila.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
});