// topbar.js — interactividad de la barra superior

document.addEventListener('DOMContentLoaded', function () {
  // Reloj en vivo (reemplaza la barra de búsqueda del topbar)
  const clockEl = document.getElementById('topbarClock');
  if (clockEl) {
    const actualizarReloj = () => {
      clockEl.textContent = new Date().toLocaleTimeString('es-CO', {
        hour: '2-digit', minute: '2-digit'
      });
    };
    actualizarReloj();
    setInterval(actualizarReloj, 1000 * 15);
  }

  // Fecha de hoy en la barra de ruta
  const dateEl = document.getElementById('breadcrumbDate');
  if (dateEl) {
    const texto = new Date().toLocaleDateString('es-CO', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });
    dateEl.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
  }

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

  // Panel de perfil: se abre al hacer clic en el perfil de la barra
  // superior, se cierra con la X, clic afuera (backdrop) o Escape.
  const btnProfile = document.getElementById('btnProfile');
  const profileDrawer = document.getElementById('profileDrawer');
  const profileBackdrop = document.getElementById('profileDrawerBackdrop');
  const profileClose = document.getElementById('profileDrawerClose');

  if (btnProfile && profileDrawer && profileBackdrop) {
    const abrirPerfil = () => {
      profileDrawer.classList.add('open');
      profileBackdrop.classList.add('open');
      btnProfile.classList.add('open');
    };
    const cerrarPerfil = () => {
      profileDrawer.classList.remove('open');
      profileBackdrop.classList.remove('open');
      btnProfile.classList.remove('open');
    };

    btnProfile.addEventListener('click', abrirPerfil);
    profileBackdrop.addEventListener('click', cerrarPerfil);
    if (profileClose) profileClose.addEventListener('click', cerrarPerfil);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') cerrarPerfil();
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
});




// sidebar.js — interactividad del panel lateral

document.addEventListener('DOMContentLoaded', function () {
  // Colapsa el panel a solo íconos (llamado desde el botón ☰ del navbar superior)
  // y recuerda la preferencia para que no "parpadee" al cambiar de página
  // (el estado inicial ya se aplica antes, en el <script> al inicio de sidebar.php).
  window.toggleSidebarCollapse = function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const colapsado = sidebar.classList.toggle('collapsed');
    try { localStorage.setItem('sidebarCollapsed', colapsado ? '1' : '0'); } catch (e) {}
  };

  // Panel deslizante en pantallas angostas (<=880px): no se guarda
  // preferencia, cada carga de página empieza cerrado.
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  window.toggleSidebarMobile = function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('mobile-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.toggle('open');
  };
  function cerrarSidebarMobile() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.remove('mobile-open');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('open');
  }
  if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', cerrarSidebarMobile);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarSidebarMobile();
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
  }
});