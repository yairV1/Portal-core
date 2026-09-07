  /* KPIs, matrícula, ejecución presupuestal y alertas ya los renderiza
     Tablero.php con datos reales desde $pdo (ver PortalController.php).
     Acá solo queda lo que sigue siendo genuinamente interactivo: las
     pestañas de vista (Rectoría/Directivos/Académico/Financiero) no
     tienen tabla propia — son navegación, no contenido. */
  const TABS = ['Rectoría', 'Directivos', 'Académico', 'Financiero'];
  document.getElementById('tableroTabs').innerHTML = TABS.map((t,i) => `
    <button type="button" class="pill-tab${i===0?' active':''}" data-tab="${t}">${t}</button>`).join('');
  document.getElementById('tableroTabs').addEventListener('click', e => {
    if (e.target.tagName !== 'BUTTON') return;
    document.querySelectorAll('#tableroTabs .pill-tab').forEach(b => b.classList.remove('active'));
    e.target.classList.add('active');
  });
