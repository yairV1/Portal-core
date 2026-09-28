/* ══════════════════════════════════════════════════════════
   core/ui.js — Comportamiento de los componentes del sistema de diseño
   (ver core/components.css y docs/design-system/index.html).
   Sin dependencias. Todo funciona por atributos en el HTML:

     data-open="idDelDialog"     abre un modal/drawer (<dialog>)
     data-close                  cierra el <dialog> que lo contiene
     data-confirm="¿Título?"     en un <form>: pide confirmación antes de enviar
       data-confirm-text="…"       descripción opcional
       data-confirm-ok="Eliminar"  texto del botón (por defecto "Confirmar")
       data-confirm-tone="danger"  danger (por defecto) | primary
     data-no-loading             en un <form>: no mostrar spinner al enviar
     data-tabs / data-tab="x" / data-tab-panel="x"   pestañas o segmentado
     .file-drop                  muestra el nombre del archivo elegido
     .table--stack               etiquetas de celda para la vista móvil
     tr[data-href]               fila navegable
     data-dismiss="alert"        cierra una .alert

   API global: UI.open(el), UI.close(el), UI.confirm({...}) → Promise<bool>,
               UI.toast({ type, title, text, duration, image })
   ══════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var UI = window.UI = window.UI || {};

  function byIdOrEl(ref) {
    return typeof ref === 'string' ? document.getElementById(ref.replace(/^#/, '')) : ref;
  }

  // ── Modal / drawer ──────────────────────────────────────
  var disparadores = new WeakMap();

  function primerCampo(dialog) {
    var auto = dialog.querySelector('[autofocus]');
    if (auto) return auto;
    var cuerpo = dialog.querySelector('.modal-body') || dialog;
    var candidatos = cuerpo.querySelectorAll('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
    for (var i = 0; i < candidatos.length; i++) {
      if (candidatos[i].offsetParent !== null) return candidatos[i];
    }
    return null;
  }

  UI.open = function (ref, trigger) {
    var dialog = byIdOrEl(ref);
    if (!dialog || typeof dialog.showModal !== 'function' || dialog.open) return;
    disparadores.set(dialog, trigger || document.activeElement);
    dialog.classList.remove('is-closing');
    dialog.showModal();
    var campo = primerCampo(dialog);
    if (campo) campo.focus({ preventScroll: true });
    dialog.dispatchEvent(new CustomEvent('ui:open'));
  };

  UI.close = function (ref, valor) {
    var dialog = byIdOrEl(ref);
    if (!dialog || !dialog.open || dialog.classList.contains('is-closing')) return;
    var terminar = function () {
      dialog.classList.remove('is-closing');
      dialog.close(valor);
      var origen = disparadores.get(dialog);
      if (origen && typeof origen.focus === 'function' && document.contains(origen)) {
        origen.focus({ preventScroll: true });
      }
    };
    var reducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducido) { terminar(); return; }
    dialog.classList.add('is-closing');
    var hecho = false;
    var fin = function () { if (hecho) return; hecho = true; terminar(); };
    dialog.addEventListener('animationend', fin, { once: true });
    setTimeout(fin, 260); // por si la animación no corre (pestaña en segundo plano)
  };

  // Esc: animar la salida en vez del cierre seco nativo.
  document.addEventListener('cancel', function (e) {
    var d = e.target;
    if (d instanceof HTMLDialogElement && (d.classList.contains('modal') || d.classList.contains('drawer'))) {
      e.preventDefault();
      if (d.dataset.static === undefined) UI.close(d);
    }
  }, true);

  // Clic en el overlay: solo si el clic EMPEZÓ y TERMINÓ fuera del panel
  // (seleccionar texto de un campo y soltar afuera no debe cerrarlo).
  var inicioEnBackdrop = false;
  document.addEventListener('pointerdown', function (e) {
    inicioEnBackdrop = e.target instanceof HTMLDialogElement && fueraDelPanel(e.target, e);
  });
  function fueraDelPanel(dialog, e) {
    var r = dialog.getBoundingClientRect();
    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
  }

  document.addEventListener('click', function (e) {
    var t = e.target;

    if (t instanceof HTMLDialogElement && t.open && inicioEnBackdrop && fueraDelPanel(t, e)
        && (t.classList.contains('modal') || t.classList.contains('drawer')) && t.dataset.static === undefined) {
      UI.close(t);
      return;
    }

    var abrir = t.closest && t.closest('[data-open]');
    if (abrir) {
      e.preventDefault();
      UI.open(abrir.getAttribute('data-open'), abrir);
      return;
    }

    var cerrar = t.closest && t.closest('[data-close]');
    if (cerrar) {
      var dlg = cerrar.closest('dialog');
      if (dlg) { e.preventDefault(); UI.close(dlg); }
      return;
    }

    var descartar = t.closest && t.closest('[data-dismiss="alert"]');
    if (descartar) {
      var alerta = descartar.closest('.alert');
      if (alerta) alerta.remove();
      return;
    }

    var fila = t.closest && t.closest('tr[data-href]');
    if (fila && !t.closest('a, button, input, select, textarea, label, summary, form')) {
      window.location.href = fila.getAttribute('data-href');
    }
  });

  // ── Confirmación ────────────────────────────────────────
  var dialogoConfirm = null;

  function crearConfirm() {
    var d = document.createElement('dialog');
    d.className = 'modal modal-sm modal-confirm';
    d.setAttribute('aria-labelledby', 'uiConfirmTitulo');
    d.setAttribute('aria-describedby', 'uiConfirmTexto');
    d.innerHTML =
      '<header class="modal-header">' +
        '<span class="modal-icon" aria-hidden="true"><i class="bi"></i></span>' +
        '<div class="modal-heading">' +
          '<h2 class="modal-title" id="uiConfirmTitulo"></h2>' +
          '<p class="modal-desc" id="uiConfirmTexto"></p>' +
        '</div>' +
      '</header>' +
      '<footer class="modal-footer">' +
        '<button type="button" class="btn" data-accion="cancelar"></button>' +
        '<button type="button" class="btn" data-accion="aceptar"></button>' +
      '</footer>';
    document.body.appendChild(d);
    return d;
  }

  UI.confirm = function (opciones) {
    opciones = opciones || {};
    if (!dialogoConfirm) dialogoConfirm = crearConfirm();
    var d = dialogoConfirm;
    var peligro = (opciones.tone || 'danger') === 'danger';
    var icono = d.querySelector('.modal-icon');
    icono.className = 'modal-icon' + (peligro ? ' modal-icon--danger' : '');
    icono.firstChild.className = 'bi ' + (opciones.icon || (peligro ? 'bi-exclamation-triangle' : 'bi-question-circle'));
    d.querySelector('.modal-title').textContent = opciones.title || '¿Confirmar esta acción?';
    var texto = d.querySelector('.modal-desc');
    texto.textContent = opciones.text || '';
    texto.hidden = !opciones.text;
    var cancelar = d.querySelector('[data-accion="cancelar"]');
    var aceptar = d.querySelector('[data-accion="aceptar"]');
    cancelar.textContent = opciones.cancelLabel || 'Cancelar';
    aceptar.textContent = opciones.confirmLabel || 'Confirmar';
    aceptar.className = 'btn ' + (peligro ? 'btn-danger' : 'btn-primary');

    return new Promise(function (resolver) {
      var resultado = false;
      function limpiar() {
        aceptar.removeEventListener('click', alAceptar);
        cancelar.removeEventListener('click', alCancelar);
        d.removeEventListener('close', alCerrar);
      }
      function alAceptar() { resultado = true; UI.close(d); }
      function alCancelar() { UI.close(d); }
      function alCerrar() { limpiar(); resolver(resultado); }
      aceptar.addEventListener('click', alAceptar);
      cancelar.addEventListener('click', alCancelar);
      d.addEventListener('close', alCerrar);
      UI.open(d, opciones.trigger);
      // Foco en "Cancelar": en una acción destructiva, Enter no debe borrar.
      (peligro ? cancelar : aceptar).focus();
    });
  };

  // <form data-confirm="…">: intercepta el envío (fase de captura, antes
  // que el spinner de carga) y lo reenvía tal cual si se confirma.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
    if (form.dataset.confirmado === '1') { delete form.dataset.confirmado; return; }
    e.preventDefault();
    e.stopImmediatePropagation();
    var submitter = e.submitter || null;
    UI.confirm({
      title: form.getAttribute('data-confirm'),
      text: form.getAttribute('data-confirm-text'),
      confirmLabel: form.getAttribute('data-confirm-ok'),
      tone: form.getAttribute('data-confirm-tone') || 'danger',
      trigger: submitter
    }).then(function (ok) {
      if (!ok) return;
      form.dataset.confirmado = '1';
      if (typeof form.requestSubmit === 'function') form.requestSubmit(submitter || undefined);
      else form.submit();
    });
  }, true);

  // ── Estado de carga al enviar formularios ──────────────
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (e.defaultPrevented || !(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) return;
    if (form.dataset.enviando === '1') { e.preventDefault(); return; } // doble clic
    form.dataset.enviando = '1';
    var boton = e.submitter || form.querySelector('button[type="submit"], button:not([type])');
    if (boton && !boton.classList.contains('modal-close')) {
      boton.classList.add('is-loading');
      boton.setAttribute('aria-busy', 'true');
    }
    // Por si la respuesta no navega (descarga, error de red): no dejar el
    // formulario bloqueado para siempre.
    setTimeout(function () {
      delete form.dataset.enviando;
      if (boton) { boton.classList.remove('is-loading'); boton.removeAttribute('aria-busy'); }
    }, 12000);
  });

  // ── Toasts ──────────────────────────────────────────────
  var ICONOS_TOAST = {
    success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill'
  };
  var region = null;

  UI.toast = function (opciones) {
    opciones = opciones || {};
    if (!region) {
      region = document.createElement('div');
      region.className = 'toast-region';
      region.setAttribute('role', 'region');
      region.setAttribute('aria-label', 'Notificaciones');
      document.body.appendChild(region);
    }
    var tipo = opciones.type || 'info';
    var duracion = opciones.duration || 4000;
    var t = document.createElement('div');
    t.className = 'toast toast-' + tipo;
    t.setAttribute('role', tipo === 'error' || tipo === 'danger' ? 'alert' : 'status');

    var visual;
    if (opciones.image) {
      visual = document.createElement('img');
      visual.className = 'toast-avatar';
      visual.src = opciones.image;
      visual.alt = '';
    } else {
      visual = document.createElement('i');
      visual.className = 'bi toast-icon ' + (ICONOS_TOAST[tipo] || ICONOS_TOAST.info);
      visual.setAttribute('aria-hidden', 'true');
    }
    var contenido = document.createElement('div');
    contenido.className = 'toast-content';
    var titulo = document.createElement('p');
    titulo.className = 'toast-title';
    titulo.textContent = opciones.title || '';
    contenido.appendChild(titulo);
    if (opciones.text) {
      var texto = document.createElement('p');
      texto.className = 'toast-text';
      texto.textContent = opciones.text;
      contenido.appendChild(texto);
    }
    var cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'toast-close';
    cerrar.setAttribute('aria-label', 'Cerrar notificación');
    cerrar.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
    var barra = document.createElement('span');
    barra.className = 'toast-progress';
    barra.style.animationDuration = duracion + 'ms';

    t.appendChild(visual); t.appendChild(contenido); t.appendChild(cerrar); t.appendChild(barra);
    region.appendChild(t);

    // El temporizador se pausa con el mouse encima (la barra también, por CSS).
    var restante = duracion, inicio = Date.now(), timer = null;
    function salir() {
      clearTimeout(timer);
      t.classList.add('is-leaving');
      setTimeout(function () { t.remove(); }, 170);
    }
    function reanudar() { inicio = Date.now(); timer = setTimeout(salir, restante); }
    t.addEventListener('mouseenter', function () { clearTimeout(timer); restante -= Date.now() - inicio; });
    t.addEventListener('mouseleave', reanudar);
    cerrar.addEventListener('click', salir);
    reanudar();
    return t;
  };

  // ── Pestañas / segmentado ───────────────────────────────
  function activarTab(boton) {
    var grupo = boton.closest('[data-tabs]');
    if (!grupo) return;
    var alcance = grupo.closest('[data-tabs-scope], dialog') || document;
    grupo.querySelectorAll('[data-tab]').forEach(function (b) {
      var activo = b === boton;
      b.classList.toggle('is-active', activo);
      b.setAttribute('aria-selected', activo ? 'true' : 'false');
      b.tabIndex = activo ? 0 : -1;
    });
    alcance.querySelectorAll('[data-tab-panel]').forEach(function (p) {
      p.hidden = p.getAttribute('data-tab-panel') !== boton.getAttribute('data-tab');
    });
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-tabs] [data-tab]');
    if (b) activarTab(b);
  });
  document.addEventListener('keydown', function (e) {
    var b = e.target.closest && e.target.closest('[data-tabs] [data-tab]');
    if (!b || (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft')) return;
    var todos = Array.prototype.slice.call(b.closest('[data-tabs]').querySelectorAll('[data-tab]'));
    var i = todos.indexOf(b) + (e.key === 'ArrowRight' ? 1 : -1);
    var siguiente = todos[(i + todos.length) % todos.length];
    siguiente.focus();
    activarTab(siguiente);
  });

  // ── Zona de archivo ─────────────────────────────────────
  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
    var zona = input.closest('.file-drop');
    if (!zona) return;
    var nombre = zona.querySelector('.file-drop-name');
    var archivo = input.files && input.files[0];
    zona.classList.toggle('has-file', !!archivo);
    if (nombre) nombre.textContent = archivo ? archivo.name : '';
  });
  ['dragenter', 'dragover'].forEach(function (ev) {
    document.addEventListener(ev, function (e) {
      var zona = e.target.closest && e.target.closest('.file-drop');
      if (zona) zona.classList.add('is-dragover');
    });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    document.addEventListener(ev, function (e) {
      var zona = e.target.closest && e.target.closest('.file-drop');
      if (zona) zona.classList.remove('is-dragover');
    });
  });

  // ── Formulario rápido desplegable ───────────────────────
  // <button data-reveal="idForm"> muestra/oculta #idForm y enfoca su primer
  // campo; un [data-reveal-cancel] dentro del formulario lo vuelve a ocultar.
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-reveal]');
    if (b) {
      var destino = document.getElementById(b.getAttribute('data-reveal'));
      if (!destino) return;
      destino.hidden = !destino.hidden;
      b.setAttribute('aria-expanded', destino.hidden ? 'false' : 'true');
      if (!destino.hidden) {
        var primero = destino.querySelector('input:not([type="hidden"]), textarea, select');
        if (primero) primero.focus();
      }
      return;
    }
    var c = e.target.closest && e.target.closest('[data-reveal-cancel]');
    if (c) {
      var form = c.closest('form');
      if (!form) return;
      form.reset();
      form.hidden = true;
      var disparador = document.querySelector('[data-reveal="' + form.id + '"]');
      if (disparador) { disparador.setAttribute('aria-expanded', 'false'); disparador.focus(); }
    }
  });

  // ── Copiar al portapapeles ──────────────────────────────
  // <button data-copy="texto" data-copy-label="Enlace copiado">
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-copy]');
    if (!b) return;
    var texto = b.getAttribute('data-copy');
    var aviso = b.getAttribute('data-copy-label') || 'Copiado al portapapeles';
    var ok = function () { UI.toast({ type: 'success', title: aviso, duration: 2200 }); };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(texto).then(ok, function () { copiarRespaldo(texto) && ok(); });
    } else if (copiarRespaldo(texto)) {
      ok();
    }
  });
  // Respaldo para HTTP sin contexto seguro (entorno local): textarea temporal.
  function copiarRespaldo(texto) {
    var t = document.createElement('textarea');
    t.value = texto; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
    document.body.appendChild(t); t.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (err) {}
    t.remove();
    return ok;
  }

  // ── Filtro de tabla en el cliente ───────────────────────
  // <input data-table-filter="idTabla"> oculta las filas del <tbody> que no
  // contienen el texto (sin distinguir tildes ni mayúsculas). Si ninguna
  // coincide, muestra el elemento [data-filter-empty="idTabla"].
  function normalizarTexto(t) {
    return (t || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }
  document.addEventListener('input', function (e) {
    var campo = e.target;
    if (!(campo instanceof HTMLInputElement) || !campo.hasAttribute('data-table-filter')) return;
    var id = campo.getAttribute('data-table-filter');
    var tabla = document.getElementById(id);
    if (!tabla) return;
    var q = normalizarTexto(campo.value.trim());
    var visibles = 0;
    tabla.querySelectorAll('tbody tr').forEach(function (tr) {
      var coincide = !q || normalizarTexto(tr.textContent).indexOf(q) !== -1;
      tr.hidden = !coincide;
      if (coincide) visibles++;
    });
    var vacio = document.querySelector('[data-filter-empty="' + id + '"]');
    if (vacio) vacio.hidden = visibles > 0;
  });

  // ── Inicialización ──────────────────────────────────────
  function etiquetarTablas(raiz) {
    (raiz || document).querySelectorAll('table.table--stack').forEach(function (tabla) {
      var titulos = Array.prototype.map.call(tabla.querySelectorAll('thead th'), function (th) {
        return th.textContent.trim();
      });
      tabla.querySelectorAll('tbody tr').forEach(function (tr) {
        var col = 0;
        Array.prototype.forEach.call(tr.children, function (td) {
          if (!td.hasAttribute('data-label') && !td.hasAttribute('colspan')) {
            td.setAttribute('data-label', titulos[col] || '');
          }
          col += td.colSpan || 1;
        });
      });
    });
  }
  UI.etiquetarTablas = etiquetarTablas;

  function iniciar() {
    etiquetarTablas();
    document.querySelectorAll('[data-tabs]').forEach(function (grupo) {
      grupo.setAttribute('role', 'tablist');
      var activo = grupo.querySelector('[data-tab].is-active, [data-tab].active') || grupo.querySelector('[data-tab]');
      grupo.querySelectorAll('[data-tab]').forEach(function (b) { b.setAttribute('role', 'tab'); });
      if (activo) activarTab(activo);
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
