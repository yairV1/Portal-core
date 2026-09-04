  /* ═══ Datos de ejemplo ═══ */
  const NORMAS = [
    { codigo: 'Acuerdo 014-2026', titulo: 'Adopta la nueva estructura organizacional de COREDUCACIÓN', tipo: 'Acuerdo', fecha: '18 jul 2026', estado: 'Vigente' },
    { codigo: 'Acuerdo 011-2026', titulo: 'Aprueba el Plan de Desarrollo Institucional 2025-2030', tipo: 'Acuerdo', fecha: '12 jun 2026', estado: 'Vigente' },
    { codigo: 'Resolución 208-2026', titulo: 'Reglamenta el proceso de evaluación de desempeño', tipo: 'Resolución', fecha: '02 jul 2026', estado: 'Vigente' },
    { codigo: 'Resolución 195-2026', titulo: 'Fija los derechos pecuniarios para el año 2026', tipo: 'Resolución', fecha: '20 ene 2026', estado: 'Vigente' },
    { codigo: 'Reglamento 05-2026', titulo: 'Reglamento Estudiantil', tipo: 'Reglamento', fecha: '20 jul 2026', estado: 'Vigente' },
    { codigo: 'Reglamento 03-2026', titulo: 'Estatuto Docente', tipo: 'Reglamento', fecha: '14 abr 2026', estado: 'Vigente' },
    { codigo: 'Acuerdo 009-2025', titulo: 'Estructura organizacional anterior', tipo: 'Acuerdo', fecha: '03 mar 2025', estado: 'Derogado' },
    { codigo: 'Ley 30 de 1992', titulo: 'Organiza el servicio público de la educación superior', tipo: 'Externa', fecha: '28 dic 1992', estado: 'Vigente' },
    { codigo: 'Decreto 1330-2019', titulo: 'Registro calificado y condiciones de calidad', tipo: 'Externa', fecha: '25 jul 2019', estado: 'Vigente' },
    { codigo: 'Resolución 021666-2019', titulo: 'Parámetros de autoevaluación y acreditación', tipo: 'Externa', fecha: '20 nov 2019', estado: 'Vigente' }
  ];

  let filtroTipo = 'todo';
  const TIPOS_NORMA = ['todo', 'Acuerdo', 'Resolución', 'Reglamento', 'Externa'];

  function renderNormaChips(){
    document.getElementById('normaChips').innerHTML = TIPOS_NORMA.map(t => `
      <button class="norma-chip ${filtroTipo===t?'active':''}" data-tipo="${t}">${t==='todo'?'Todas':t}</button>`).join('');
    document.querySelectorAll('.norma-chip').forEach(btn => {
      btn.addEventListener('click', () => { filtroTipo = btn.dataset.tipo; renderNormaChips(); renderTabla(); });
    });
  }

  function renderTabla(){
    const filtradas = filtroTipo === 'todo' ? NORMAS : NORMAS.filter(n => n.tipo === filtroTipo);
    document.getElementById('normasBody').innerHTML = filtradas.map(n => `
      <tr>
        <td style="font-weight:800;white-space:nowrap">${n.codigo}</td>
        <td>${n.titulo}</td>
        <td style="opacity:.7">${n.tipo}</td>
        <td style="opacity:.7;white-space:nowrap">${n.fecha}</td>
        <td><span class="tag ${n.estado==='Vigente'?'tag-ok':'tag-neutral'}">${n.estado}</span></td>
      </tr>`).join('');
  }

  renderNormaChips();
  renderTabla();
