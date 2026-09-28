  /* ═══ Datos de ejemplo ═══ */
  const APPS = [
    { nombre: 'Correo institucional', categoria: 'Comunicación · Google Workspace', icon: '<i class="bi bi-envelope"></i>' },
    { nombre: 'Google Workspace', categoria: 'Productividad', icon: '<i class="bi bi-globe"></i>' },
    { nombre: 'Microsoft 365', categoria: 'Productividad', icon: '<i class="bi bi-grid"></i>' },
    { nombre: 'ERP Institucional', categoria: 'Administrativo', icon: '<i class="bi bi-box-seam"></i>' },
    { nombre: 'Software Contable', categoria: 'Financiero', icon: '<i class="bi bi-cash-coin"></i>' },
    { nombre: 'Software Académico', categoria: 'Académico', icon: '<i class="bi bi-mortarboard"></i>' },
    { nombre: 'Campus Virtual', categoria: 'Académico', icon: '<i class="bi bi-journal-bookmark"></i>' },
    { nombre: 'Biblioteca digital', categoria: 'Académico', icon: '<i class="bi bi-book"></i>' },
    { nombre: 'Power BI', categoria: 'Analítica', icon: '<i class="bi bi-bar-chart"></i>' },
    { nombre: 'CRM Institucional', categoria: 'Gestión comercial', icon: '<i class="bi bi-people"></i>' },
    { nombre: 'Mesa de ayuda TI', categoria: 'Soporte', icon: '<i class="bi bi-tools"></i>' },
    { nombre: 'Suite ISO', categoria: 'Sistema de Gestión Integral', icon: '<i class="bi bi-folder2-open"></i>' }
  ];

  // Favoritos: preferencia de cada persona en su navegador (antes se
  // perdían al recargar). Los accesos todavía no abren el sistema real —
  // ver el aviso de Aplicaciones.php — así que la tarjeta no finge hacerlo.
  const CLAVE_FAV = 'appsFavoritas';
  let favoritos = new Set();
  try { favoritos = new Set(JSON.parse(localStorage.getItem(CLAVE_FAV) || '[]')); } catch (e) {}
  const guardar = () => { try { localStorage.setItem(CLAVE_FAV, JSON.stringify([...favoritos])); } catch (e) {} };

  function renderApps(){
    const orden = APPS.map((a, i) => ({ a, i })).sort((x, y) => (favoritos.has(y.i) - favoritos.has(x.i)));
    document.getElementById('appsGrid').innerHTML = orden.map(({ a, i }) => `
      <div class="app-card">
        <div class="app-card-top">
          <span class="app-icon" aria-hidden="true">${a.icon}</span>
          <button type="button" class="btn btn-ghost btn-sm btn-icon app-star${favoritos.has(i) ? ' fav' : ''}" data-star="${i}"
                  aria-pressed="${favoritos.has(i)}" aria-label="Marcar ${a.nombre} como favorita">
            <i class="bi ${favoritos.has(i) ? 'bi-star-fill' : 'bi-star'}" aria-hidden="true"></i>
          </button>
        </div>
        <span class="app-nombre">${a.nombre}</span>
        <span class="app-categoria">${a.categoria}</span>
        <span class="badge badge-neutral app-estado">Por conectar</span>
      </div>`).join('');

    document.querySelectorAll('.app-star').forEach(el => {
      el.addEventListener('click', () => {
        const idx = Number(el.dataset.star);
        favoritos.has(idx) ? favoritos.delete(idx) : favoritos.add(idx);
        guardar();
        renderApps();
        const mismo = document.querySelector('.app-star[data-star="' + idx + '"]');
        if (mismo) mismo.focus();
      });
    });
  }
  renderApps();
