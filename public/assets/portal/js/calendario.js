(function () {
  var popover = document.getElementById('calPopover');
  if (!popover) return; // nada que crear — ver Calendario.php ($puedeCrear)

  var POP_WIDTH = 280; // mismo valor que .cal-popover en calendario.css
  var form = document.getElementById('calPopForm');
  var deleteForm = document.getElementById('calPopDeleteForm');
  var tituloModo = document.getElementById('calPopTituloModo');
  var submitBtn = document.getElementById('calPopSubmitBtn');
  var idInput = document.getElementById('calPopId');
  var deleteIdInput = document.getElementById('calPopDeleteId');
  var tituloInput = document.getElementById('calPopTitulo');
  var fechaInput = document.getElementById('calPopFecha');
  var horaLugarInput = document.getElementById('calPopHoraLugar');
  var mesInput = form.querySelector('input[name="mes"]');
  var mes = mesInput.value;

  var CREAR_URL = CAL_BASE_URL + '/calendario/crear-evento';
  var EDITAR_URL = CAL_BASE_URL + '/calendario/editar-evento';

  var agendaLista = document.getElementById('calAgendaLista');
  var agendaFecha = document.getElementById('calAgendaFecha');
  var eventosPorDia = (typeof EVENTOS_POR_DIA !== 'undefined') ? EVENTOS_POR_DIA : {};
  var mesTitulo = (typeof CAL_MES_TITULO !== 'undefined') ? CAL_MES_TITULO : '';
  var miId = (typeof CAL_MI_ID !== 'undefined') ? CAL_MI_ID : null;
  var esAdmin = (typeof CAL_ES_ADMIN !== 'undefined') ? CAL_ES_ADMIN : false;

  function pad(n) { return String(n).padStart(2, '0'); }
  function puedeEditar(ev) { return esAdmin || ev.usuario_id === miId; }

  // ── Selector de visibilidad (pill-tabs, dentro del formulario) ──
  var visibilidadGrupo = document.getElementById('calPopVisibilidad');
  var visibilidadInput = document.getElementById('calPopVisibilidadInput');
  function marcarVisibilidad(valor) {
    if (!visibilidadGrupo) return;
    visibilidadGrupo.querySelectorAll('.pill-tab').forEach(function (b) {
      b.classList.toggle('active', b.dataset.valor === valor);
    });
    visibilidadInput.value = valor;
  }
  if (visibilidadGrupo) {
    visibilidadGrupo.querySelectorAll('.pill-tab').forEach(function (boton) {
      boton.addEventListener('click', function () { marcarVisibilidad(boton.dataset.valor); });
    });
  }

  // ── Agenda del día / del mes ──
  // Construida con DOM/textContent, no innerHTML: el título de un evento
  // público lo escribió otro usuario, así que se trata como texto, nunca
  // como HTML (evita XSS almacenado vía el título de un evento).
  function icono(nombreClase, titulo) {
    var i = document.createElement('i');
    i.className = 'bi ' + nombreClase;
    if (titulo) i.title = titulo;
    return i;
  }

  // El enlace .ics nunca va anidado dentro del <button> de editar (<a>
  // dentro de <button> es HTML inválido) — van como hermanos, igual que
  // arma cal_render_evento_item() en Calendario.php.
  function crearItemEvento(ev, conFecha) {
    var esPrivado = ev.visibilidad === 'privado';
    var esEditable = puedeEditar(ev);

    var item = document.createElement('div');
    item.className = 'agenda-item' + (esPrivado ? ' agenda-item-privado' : '');

    var info = document.createElement(esEditable ? 'button' : 'div');
    info.className = 'agenda-item-info';
    if (esEditable) info.type = 'button';

    var texto = document.createElement('span');
    texto.className = 'agenda-item-text';

    var t = document.createElement('span');
    t.className = 'agenda-item-t';
    if (esPrivado) t.appendChild(icono('bi-lock-fill', 'Privado'));
    t.appendChild(document.createTextNode((esPrivado ? ' ' : '') + ev.titulo));
    texto.appendChild(t);

    var sub = [];
    if (conFecha) {
      var f = new Date(ev.fecha + 'T00:00:00');
      sub.push(pad(f.getDate()) + '/' + pad(f.getMonth() + 1));
    }
    if (ev.hora_lugar) sub.push(ev.hora_lugar);
    if (sub.length) {
      var h = document.createElement('span');
      h.className = 'agenda-item-h';
      h.textContent = sub.join(' · ');
      texto.appendChild(h);
    }
    info.appendChild(texto);

    if (esEditable) {
      info.appendChild(icono('bi-pencil-fill agenda-item-editar', 'Editar'));
      info.addEventListener('click', function () {
        abrirEditar(info.getBoundingClientRect(), ev);
      });
    }
    item.appendChild(info);

    var ics = document.createElement('a');
    ics.className = 'evento-ics';
    ics.href = CAL_BASE_URL + '/calendario/exportar?id=' + encodeURIComponent(ev.id);
    ics.title = 'Agregar a mi calendario (.ics)';
    ics.appendChild(icono('bi-calendar-plus'));
    item.appendChild(ics);

    return item;
  }

  function renderAgendaDia(dia, esHoy) {
    agendaFecha.textContent = (esHoy ? 'Hoy · ' : '') + dia + ' de ' + mesTitulo;
    agendaLista.innerHTML = '';

    var eventos = eventosPorDia[dia] || [];
    if (!eventos.length) {
      var vacia = document.createElement('p');
      vacia.className = 'text-muted cal-agenda-vacia';
      vacia.textContent = 'Sin eventos para este día.';
      agendaLista.appendChild(vacia);
      return;
    }
    eventos.forEach(function (ev) { agendaLista.appendChild(crearItemEvento(ev, false)); });
  }

  // ── Pestañas "Hoy" / "Este mes" ──
  // El panel de "Este mes" ya viene renderizado desde PHP (mismos datos
  // que EVENTOS_POR_DIA) — acá solo se alternan, sin volver a construirlo.
  var tabs = document.getElementById('calAgendaTabs');
  var panelDia = document.getElementById('calPanelDia');
  var panelMes = document.getElementById('calPanelMes');
  if (tabs) {
    tabs.querySelectorAll('.pill-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.querySelectorAll('.pill-tab').forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var esDia = tab.dataset.tab === 'dia';
        panelDia.hidden = !esDia;
        panelMes.hidden = esDia;
      });
    });
  }

  // ── Popover: crear vs. editar ──
  function posicionar(rect) {
    var MARGEN = 12;

    // Hay que hacerlo visible para poder medir su alto real (varía según
    // si hay selector de visibilidad o botón "Eliminar") — se posiciona
    // fuera de pantalla primero para que no se alcance a ver el salto.
    popover.style.left = '-9999px';
    popover.style.top = '0px';
    popover.hidden = false;
    var altura = popover.offsetHeight;

    var left = rect.left;
    var maxLeft = window.innerWidth - POP_WIDTH - MARGEN;
    if (left > maxLeft) left = Math.max(MARGEN, maxLeft);

    // Antes esto era siempre "rect.bottom + 8": si la celda o el botón
    // que abre el popover están cerca del final de la página, el
    // formulario (y el botón "Crear") quedaba fuera de la pantalla y no
    // se podía ni ver ni clicar. Ahora, si no cabe abajo, se intenta
    // arriba de la celda; si tampoco cabe entero, se pega al borde que
    // más espacio deje — nunca se deja el botón inalcanzable.
    var top = rect.bottom + 8;
    if (top + altura > window.innerHeight - MARGEN) {
      var arriba = rect.top - altura - 8;
      top = arriba >= MARGEN ? arriba : Math.max(MARGEN, window.innerHeight - altura - MARGEN);
    }

    popover.style.left = left + 'px';
    popover.style.top = top + 'px';
  }

  function abrirCrear(rect, dia) {
    form.reset();
    form.action = CREAR_URL;
    idInput.value = '';
    tituloModo.innerHTML = '';
    tituloModo.appendChild(icono('bi-calendar-event'));
    tituloModo.appendChild(document.createTextNode(' Nuevo evento'));
    submitBtn.innerHTML = '';
    submitBtn.appendChild(icono('bi-check-lg'));
    submitBtn.appendChild(document.createTextNode(' Crear evento'));
    deleteForm.hidden = true;
    marcarVisibilidad('publico');
    if (dia) fechaInput.value = mes + '-' + pad(dia);
    posicionar(rect);
    tituloInput.focus();
  }

  function abrirEditar(rect, ev) {
    form.action = EDITAR_URL;
    idInput.value = ev.id;
    tituloInput.value = ev.titulo;
    fechaInput.value = ev.fecha;
    horaLugarInput.value = ev.hora_lugar || '';
    if (visibilidadGrupo) marcarVisibilidad(ev.visibilidad);
    tituloModo.innerHTML = '';
    tituloModo.appendChild(icono('bi-pencil-fill'));
    tituloModo.appendChild(document.createTextNode(' Editar evento'));
    submitBtn.innerHTML = '';
    submitBtn.appendChild(icono('bi-check-lg'));
    submitBtn.appendChild(document.createTextNode(' Guardar cambios'));
    deleteForm.hidden = false;
    deleteIdInput.value = ev.id;
    posicionar(rect);
    tituloInput.focus();
  }

  function cerrar() {
    popover.hidden = true;
  }

  document.querySelectorAll('.cal-celda[data-dia]').forEach(function (celda) {
    celda.addEventListener('click', function () {
      var dia = parseInt(celda.dataset.dia, 10);
      renderAgendaDia(dia, celda.classList.contains('cal-hoy'));
      abrirCrear(celda.getBoundingClientRect(), dia);
    });
  });

  var btnNuevo = document.getElementById('calNuevoBtn');
  if (btnNuevo) {
    btnNuevo.addEventListener('click', function () {
      abrirCrear(btnNuevo.getBoundingClientRect(), null);
    });
  }

  // Los eventos ya renderizados desde PHP (agenda de hoy y "este mes")
  // también abren el popover en modo edición — mismo dataset que usa
  // cal_render_evento_item() en Calendario.php.
  document.querySelectorAll('[data-editar-evento]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      abrirEditar(boton.getBoundingClientRect(), {
        id: boton.dataset.id,
        titulo: boton.dataset.titulo,
        fecha: boton.dataset.fecha,
        hora_lugar: boton.dataset.horaLugar,
        visibilidad: boton.dataset.visibilidad,
      });
    });
  });

  document.getElementById('calPopClose').addEventListener('click', cerrar);

  document.addEventListener('click', function (e) {
    if (popover.hidden) return;
    if (popover.contains(e.target)) return;
    if (e.target.closest('.cal-celda[data-dia]')) return;
    if (e.target.closest('#calNuevoBtn')) return;
    if (e.target.closest('[data-editar-evento]')) return;
    cerrar();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrar();
  });
})();
