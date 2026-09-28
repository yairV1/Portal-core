// paneles.js — interactividad del layout del Portal (barra superior, tema,
// Mi perfil, cerrar sesión y panel lateral).

document.addEventListener('DOMContentLoaded', function () {
  // Modo día/noche automático: 06:00–18:00 claro, el resto oscuro.
  // Si el usuario nunca tocó el botón, se revisa cada minuto y se ajusta
  // solo (por si el portal queda abierto y cruza las 6am/6pm). En cuanto
  // el usuario hace clic, esa elección manual queda guardada y ya no se
  // vuelve a tocar automáticamente.
  function temaPorHora() {
    const hora = new Date().getHours();
    return (hora >= 6 && hora < 18) ? 'claro' : 'oscuro';
  }
  function aplicarTemaAutomatico() {
    let manual = null;
    try { manual = localStorage.getItem('tema'); } catch (e) {}
    if (manual === 'claro' || manual === 'oscuro') return;
    document.body.dataset.tema = temaPorHora();
  }
  aplicarTemaAutomatico();
  setInterval(aplicarTemaAutomatico, 60 * 1000);

  // El ícono luna/sol se sincroniza solo por CSS según data-tema (ver paneles.css),
  // así que aquí solo hace falta alternar el atributo y guardar la preferencia.
  const btnTheme = document.getElementById('btnTheme');
  if (btnTheme) {
    btnTheme.addEventListener('click', function () {
      const oscuro = document.body.dataset.tema !== 'oscuro';
      document.body.dataset.tema = oscuro ? 'oscuro' : 'claro';
      try { localStorage.setItem('tema', oscuro ? 'oscuro' : 'claro'); } catch (e) {}
    });
  }

  // Mi perfil: es un drawer del sistema de diseño (<dialog>, ver
  // portal-header.php) — abrir/cerrar, Esc, overlay y foco los maneja
  // core/ui.js vía data-open/data-close. Acá solo queda alternar entre ver
  // los datos y el formulario de edición, sin recargar.
  const btnProfile = document.getElementById('btnProfile');
  const profileDrawer = document.getElementById('profileDrawer');
  const perfilVista = document.getElementById('perfilVista');
  const perfilVistaAcciones = document.getElementById('perfilVistaAcciones');
  const perfilForm = document.getElementById('perfilForm');
  const perfilDesc = document.getElementById('perfilDesc');
  const btnEditarPerfil = document.getElementById('btnEditarPerfil');
  const btnCancelarPerfil = document.getElementById('btnCancelarPerfil');

  function modoEdicionPerfil(editar) {
    if (!perfilVista || !perfilForm) return;
    perfilVista.hidden = editar;
    if (perfilVistaAcciones) perfilVistaAcciones.hidden = editar;
    perfilForm.hidden = !editar;
    if (perfilDesc) perfilDesc.textContent = editar ? 'Actualiza tu nombre y tu foto.' : 'Tu información en el Portal CORE.';
    if (editar) {
      const nombre = document.getElementById('perfilNombre');
      if (nombre) nombre.focus();
    } else if (btnEditarPerfil) {
      btnEditarPerfil.focus();
    }
  }
  if (btnEditarPerfil) btnEditarPerfil.addEventListener('click', function () { modoEdicionPerfil(true); });
  if (btnCancelarPerfil) btnCancelarPerfil.addEventListener('click', function () { modoEdicionPerfil(false); });
  if (profileDrawer) {
    profileDrawer.addEventListener('ui:open', function () { if (btnProfile) btnProfile.classList.add('open'); });
    // Al cerrar, siempre vuelve a la vista de datos (no queda "a medio editar").
    profileDrawer.addEventListener('close', function () {
      if (btnProfile) btnProfile.classList.remove('open');
      if (perfilForm && !perfilForm.hidden) {
        perfilVista.hidden = false;
        if (perfilVistaAcciones) perfilVistaAcciones.hidden = false;
        perfilForm.hidden = true;
        if (perfilDesc) perfilDesc.textContent = 'Tu información en el Portal CORE.';
      }
    });
  }

  // Vista previa de la foto elegida, antes de guardar.
  const perfilFotoInput = document.getElementById('perfilFoto');
  if (perfilFotoInput) {
    perfilFotoInput.addEventListener('change', function () {
      const archivo = perfilFotoInput.files && perfilFotoInput.files[0];
      if (!archivo) return;
      const contenedor = document.querySelector('.profile-drawer-avatar-img');
      if (!contenedor) return;
      const lector = new FileReader();
      lector.onload = function () {
        contenedor.textContent = '';
        const img = document.createElement('img');
        img.src = lector.result;
        img.alt = '';
        contenedor.appendChild(img);
      };
      lector.readAsDataURL(archivo);
    });
  }

  // Botón ☰ del navbar: en escritorio colapsa el sidebar a íconos; en
  // pantallas angostas (sidebar ya es un panel deslizante, ver paneles.css)
  // lo abre/cierra en su lugar. Ambas funciones viven en sidebar.js.
  const btnToggleNav = document.getElementById('btnToggleNav');
  if (btnToggleNav) {
    btnToggleNav.addEventListener('click', function () {
      const esMobile = window.matchMedia('(max-width: 880px)').matches;
      if (esMobile && typeof window.toggleSidebarMobile === 'function') {
        window.toggleSidebarMobile();
      } else if (typeof window.toggleSidebarCollapse === 'function') {
        window.toggleSidebarCollapse();
      }
    });
  }

  // Confirmación antes de cerrar sesión (diálogo del sistema, core/ui.js).
  // /logout exige POST+CSRF (ver AuthController.php), así que el logout
  // real siempre es el envío de #formCerrarSesion. El aviso de "sesión
  // cerrada" lo muestra login.php al volver (?salida=1).
  const btnCerrarSesion = document.getElementById('btnCerrarSesion');
  const formCerrarSesion = document.getElementById('formCerrarSesion');
  if (btnCerrarSesion && formCerrarSesion) {
    btnCerrarSesion.addEventListener('click', function (e) {
      e.preventDefault();
      if (!window.UI || typeof UI.confirm !== 'function') {
        formCerrarSesion.submit();
        return;
      }
      UI.confirm({
        title: '¿Cerrar sesión?',
        text: 'Tendrás que volver a ingresar tu correo y contraseña.',
        confirmLabel: 'Cerrar sesión',
        icon: 'bi-box-arrow-right',
        trigger: btnCerrarSesion
      }).then(function (ok) {
        if (ok) formCerrarSesion.submit();
      });
    });
  }
});




// sidebar.js — interactividad del panel lateral

document.addEventListener('DOMContentLoaded', function () {
  // Colapsa el panel a solo íconos (llamado desde el botón ☰ del navbar superior)
  // y recuerda la preferencia para que no "parpadee" al cambiar de página
  // (el estado inicial ya se aplica antes, en el <script> al inicio de sidebar.php).
  // aria-expanded del botón ☰ refleja si el menú se ve completo: en
  // escritorio, no contraído; en móvil, el panel deslizante abierto.
  const btnMenu = document.getElementById('btnToggleNav');
  function sincronizarBotonMenu() {
    const sidebar = document.getElementById('sidebar');
    if (!btnMenu || !sidebar) return;
    const esMobile = window.matchMedia('(max-width: 880px)').matches;
    const expandido = esMobile ? sidebar.classList.contains('mobile-open') : !sidebar.classList.contains('collapsed');
    btnMenu.setAttribute('aria-expanded', expandido ? 'true' : 'false');
  }
  sincronizarBotonMenu();
  window.matchMedia('(max-width: 880px)').addEventListener('change', sincronizarBotonMenu);

  window.toggleSidebarCollapse = function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const colapsado = sidebar.classList.toggle('collapsed');
    try { localStorage.setItem('sidebarCollapsed', colapsado ? '1' : '0'); } catch (e) {}
    sincronizarBotonMenu();
  };

  // Panel deslizante en pantallas angostas (<=880px): no se guarda
  // preferencia, cada carga de página empieza cerrado.
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  window.toggleSidebarMobile = function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('mobile-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.toggle('open');
    sincronizarBotonMenu();
  };
  function cerrarSidebarMobile() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.remove('mobile-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('open');
    sincronizarBotonMenu();
  }
  if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', cerrarSidebarMobile);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarSidebarMobile();
  });

  // Submenús de dirección (ej. "Gestión Institucional" → "Mejoras"): el
  // atributo data-bs-toggle="collapse" es solo semántico acá — este
  // proyecto no carga el JS de Bootstrap, así que el toggle real es este.
  document.querySelectorAll('.sidebar-group > [data-bs-toggle="collapse"]').forEach(function (trigger) {
    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      const panel = document.getElementById(trigger.getAttribute('aria-controls'));
      if (!panel) return;
      const abierto = panel.classList.toggle('show');
      trigger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });
  });

  // Tooltip con el nombre del ítem al pasar el mouse, cuando el sidebar está
  // contraído. Va con position:fixed y se posiciona aquí por JS porque un
  // tooltip absolute dentro de .sidebar-scroll queda recortado por su scroll.
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    const tip = document.createElement('div');
    tip.className = 'sidebar-tooltip';
    document.body.appendChild(tip);

    sidebar.querySelectorAll('.sidebar-item').forEach(function (item) {
      const label = item.querySelector('.label');
      if (!label) return;
      // Los que abren un grupo muestran el flyout de abajo (ya trae su
      // propio título con el nombre), no hace falta este tooltip simple.
      if (item.closest('.sidebar-group')) return;

      item.addEventListener('mouseenter', function () {
        if (!sidebar.classList.contains('collapsed')) return;
        const rect = item.getBoundingClientRect();
        tip.textContent = label.textContent;
        tip.style.left = (rect.right + 10) + 'px';
        tip.style.top = (rect.top + rect.height / 2) + 'px';
        tip.style.transform = 'translateY(-50%)';
        tip.classList.add('visible');
      });
      item.addEventListener('mouseleave', function () {
        tip.classList.remove('visible');
      });
    });

    // Flyout con las áreas reales de cada dirección cuando el sidebar está
    // contraído: ahí el .collapse normal queda oculto (no hay ancho para
    // las etiquetas), así que sin esto esas áreas serían inalcanzables sin
    // expandir el panel primero. Mismo truco de position:fixed que el
    // tooltip de arriba. Un pequeño retraso al ocultar (en vez de al
    // instante) para que el mouse pueda cruzar del ícono al flyout sin que
    // se cierre a mitad de camino.
    const flyout = document.createElement('div');
    flyout.className = 'sidebar-flyout';
    document.body.appendChild(flyout);
    let flyoutHideTimer = null;

    function ocultarFlyoutConDelay() {
      flyoutHideTimer = setTimeout(function () {
        flyout.classList.remove('visible');
      }, 150);
    }

    sidebar.querySelectorAll('.sidebar-group').forEach(function (grupo) {
      const trigger = grupo.querySelector(':scope > .sidebar-item');
      const panel = grupo.querySelector(':scope > .collapse');
      if (!trigger || !panel) return;
      const links = panel.querySelectorAll('.sidebar-subitem');
      if (!links.length) return;
      const tituloLabel = trigger.querySelector('.label');

      trigger.addEventListener('mouseenter', function () {
        if (!sidebar.classList.contains('collapsed')) return;
        clearTimeout(flyoutHideTimer);
        flyout.innerHTML = '';
        if (tituloLabel) {
          const titulo = document.createElement('div');
          titulo.className = 'sidebar-flyout-title';
          titulo.textContent = tituloLabel.textContent;
          flyout.appendChild(titulo);
        }
        links.forEach(function (a) { flyout.appendChild(a.cloneNode(true)); });
        const rect = trigger.getBoundingClientRect();
        flyout.style.left = (rect.right + 10) + 'px';
        flyout.style.top = (rect.top + rect.height / 2) + 'px';
        flyout.classList.add('visible');
      });
      trigger.addEventListener('mouseleave', ocultarFlyoutConDelay);
    });
    flyout.addEventListener('mouseenter', function () { clearTimeout(flyoutHideTimer); });
    flyout.addEventListener('mouseleave', ocultarFlyoutConDelay);
  }
});