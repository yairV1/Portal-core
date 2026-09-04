  /* ═══ Datos de ejemplo — Vicerrectoría Académica ═══ */
  const MODULO = {
    kicker: 'Vicerrectoría Académica',
    titulo: 'Gestión Académica',
    desc: 'Programas, decanaturas, registro y control académico, reglamentos, consejos y planeación académica.',
    kpis: [
      { label: 'Estudiantes', valor: '8.742' },
      { label: 'Programas', valor: '11' },
      { label: 'Docentes', valor: '412' },
      { label: 'Retención', valor: '89,4%' }
    ],
    areas: [
      { label: 'Decanatura de Tecnologías y Transformación Digital', meta: '3 programas · 2.410 estudiantes' },
      { label: 'Decanatura de Ciencias Administrativas, Contables y Financieras', meta: '4 programas · 3.180 estudiantes' },
      { label: 'Decanatura de Infraestructura, Desarrollo y Sostenibilidad', meta: '2 programas · 1.520 estudiantes' },
      { label: 'Decanatura del Centro de Idiomas e Internacionalización', meta: 'Cursos · Movilidad' },
      { label: 'Registro y Control Académico', meta: 'Matrícula · Certificaciones' }
    ],
    docs: [
      { nombre: 'Reglamento Estudiantil', tipo: 'Reglamento', version: 'V5.0', fecha: '20 jul 2026' },
      { nombre: 'Calendario Académico 2026-II', tipo: 'Cronograma', version: 'V1.1', fecha: '01 jul 2026' },
      { nombre: 'Estatuto Docente', tipo: 'Reglamento', version: 'V3.0', fecha: '14 abr 2026' },
      { nombre: 'Plan de Autoevaluación Institucional', tipo: 'Plan', version: 'V2.0', fecha: '28 may 2026' }
    ],
    responsables: [
      { ini: 'CN', nombre: 'Camilo Naranjo', cargo: 'Vicerrector Académico' },
      { ini: 'YT', nombre: 'Yolanda Torres', cargo: 'Registro y Control Académico' }
    ],
    software: ['Software Académico', 'Campus Virtual', 'Biblioteca digital']
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
