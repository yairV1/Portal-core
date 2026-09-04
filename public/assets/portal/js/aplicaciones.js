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

  const favoritos = new Set(); // ids marcados con estrella

  function renderApps(){
    document.getElementById('appsGrid').innerHTML = APPS.map((a,i) => `
      <div class="app-card" data-idx="${i}">
        <div class="app-card-top">
          <span class="app-icon">${a.icon}</span>
          <span class="app-star ${favoritos.has(i)?'fav':''}" data-star="${i}"><i class="bi bi-star-fill"></i></span>
        </div>
        <span class="app-nombre">${a.nombre}</span>
        <span class="app-categoria">${a.categoria}</span>
        <span class="app-abrir">Abrir ↗</span>
      </div>`).join('');

    document.querySelectorAll('.app-star').forEach(el => {
      el.addEventListener('click', (e) => {
        e.stopPropagation();
        const idx = Number(el.dataset.star);
        favoritos.has(idx) ? favoritos.delete(idx) : favoritos.add(idx);
        renderApps();
      });
    });
    document.querySelectorAll('.app-card').forEach(el => {
      el.addEventListener('click', () => {
        alert('Abrir "' + APPS[el.dataset.idx].nombre + '" (login federado — se conecta con el sistema real)');
      });
    });
  }
  renderApps();
