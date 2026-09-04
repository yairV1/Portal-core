  /* ═══ Datos de ejemplo — Novedades ═══ */
  const MODULO = {
    kicker: 'Comunicaciones Institucionales',
    titulo: 'Novedades',
    desc: 'Noticias, comunicados, circulares, eventos, reconocimientos y alertas institucionales.',
    kpis: [
      { label: 'Publicaciones mes', valor: '34' },
      { label: 'Circulares 2026', valor: '61' },
      { label: 'Eventos próximos', valor: '9' },
      { label: 'Lectura promedio', valor: '78%' }
    ],
    areas: [
      { label: 'Noticias', meta: 'Actualidad institucional' },
      { label: 'Comunicados y circulares', meta: 'Rectoría · Secretaría General' },
      { label: 'Eventos', meta: 'Agenda institucional' },
      { label: 'Reconocimientos', meta: 'Talento que suma' }
    ],
    docs: [
      { nombre: 'Circular 061 · Cierre académico 2026-II', tipo: 'Circular', version: 'V1.0', fecha: '29 jul 2026' },
      { nombre: 'Comunicado Rectoría · Acreditación', tipo: 'Comunicado', version: 'V1.0', fecha: '24 jul 2026' },
      { nombre: 'Boletín Institucional Julio', tipo: 'Boletín', version: 'V1.0', fecha: '18 jul 2026' }
    ],
    responsables: [
      { ini: 'VS', nombre: 'Valentina Suárez', cargo: 'Comunicaciones' }
    ],
    software: ['Correo institucional', 'Google Workspace']
  };

  document.getElementById('moduloKicker').textContent = MODULO.kicker;
  document.getElementById('moduloTitulo').textContent = MODULO.titulo;
  document.getElementById('moduloDesc').textContent = MODULO.desc;

  document.getElementById('moduloKpis').innerHTML = MODULO.kpis.map(k => `
    <div class="modulo-kpi"><div class="label">${k.label}</div><div class="valor">${k.valor}</div></div>`).join('');

  document.getElementById('moduloAreas').innerHTML = MODULO.areas.map(a => `
    <div class="area-item">
      <span class="ic"><i class="bi bi-folder2"></i></span>
      <span style="flex:1">
        <span class="label">${a.label}</span>
        <span class="meta">${a.meta}</span>
      </span>
      <span class="arrow">→</span>
    </div>`).join('');

  document.getElementById('moduloDocs').innerHTML = MODULO.docs.map(d => `
    <tr>
      <td><strong>${d.nombre}</strong></td>
      <td style="opacity:.7">${d.tipo}</td>
      <td><span class="tag">${d.version}</span></td>
      <td style="opacity:.7">${d.fecha}</td>
    </tr>`).join('');

  document.getElementById('moduloResponsables').innerHTML = MODULO.responsables.map(r => `
    <div class="responsable">
      <span class="ini">${r.ini}</span>
      <span style="flex:1">
        <span class="nombre">${r.nombre}</span>
        <span class="cargo">${r.cargo}</span>
      </span>
    </div>`).join('');

  document.getElementById('moduloSoftware').innerHTML = MODULO.software.map(s => `
    <div class="software-item"><span style="opacity:.6"><i class="bi bi-link-45deg"></i></span>${s}</div>`).join('');
