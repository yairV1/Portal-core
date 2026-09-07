// asistente.js — "Core", la mascota-asistente del Portal CORE.
//
// Por ahora responde por coincidencia de palabras clave contra las
// secciones que ya existen en el portal (sin IA real todavía — se pensó
// así a propósito para poder conectarla más adelante a la API de Claude
// sin tener que rehacer esta interfaz: solo cambiaría la función
// responder() por una llamada al backend).

document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('btnAsistente');
  var panel = document.getElementById('asistentePanel');
  if (!btn || !panel) return;

  var base = window.BASE_URL || '';

  var DESTINOS = [
    { label: 'Inicio', url: base + '/', kw: ['inicio', 'dashboard', 'panel', 'resumen', 'pendientes', 'cumpleanos', 'agenda', 'kpi'] },
    { label: 'Tableros Estratégicos', url: base + '/tableros', kw: ['tablero', 'tableros', 'indicadores', 'estadisticas', 'power bi', 'matricula'] },
    { label: 'Mapa del portal', url: base + '/mapa-portal', kw: ['mapa', 'sitemap', 'estructura', 'arquitectura de informacion'] },
    { label: 'Gestión Institucional', url: base + '/gestion-institucional', kw: ['gestion institucional', 'rectoria', 'secretaria general'] },
    { label: 'Sistema de Gestión Integral', url: base + '/sgi', kw: ['sgi', 'calidad', 'procesos', 'iso', 'auditoria', 'riesgos', 'mejoramiento'] },
    { label: 'Vicerrectoría Académica', url: base + '/vicerrectoria-academica', kw: ['academica', 'vicerrectoria', 'programas', 'docentes', 'decanaturas'] },
    { label: 'Administrativa y Financiera', url: base + '/administrativa-financiera', kw: ['financiera', 'finanzas', 'presupuesto', 'contable', 'administrativa', 'pagos', 'ejecucion presupuestal'] },
    { label: 'Talento Humano', url: base + '/talento-humano', kw: ['talento humano', 'recursos humanos', 'nomina', 'personal', 'contratacion', 'vacaciones', 'organigrama', 'bienestar', 'desempeno'] },
    { label: 'Investigación e Innovación', url: base + '/investigacion-innovacion', kw: ['investigacion', 'innovacion', 'proyectos', 'semilleros'] },
    { label: 'Gestión Documental', url: base + '/gestion-documental', kw: ['documentos', 'archivo', 'documental', 'repositorio'] },
    { label: 'Normatividad', url: base + '/normatividad', kw: ['normatividad', 'acuerdos', 'resoluciones', 'reglamento', 'politicas', 'norma'] },
    { label: 'Novedades', url: base + '/novedades', kw: ['novedades', 'noticias', 'comunicados', 'eventos', 'circulares'] },
    { label: 'Aplicaciones', url: base + '/aplicaciones', kw: ['aplicaciones', 'apps', 'correo', 'google', 'microsoft', 'sistemas', 'centro de aplicaciones'] }
  ];

  function normalizar(s) {
    return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
  }

  function buscarDestino(pregunta) {
    var q = normalizar(pregunta);
    if (!q) return null;
    var mejor = null, mejorPeso = 0;
    DESTINOS.forEach(function (d) {
      d.kw.forEach(function (k) {
        var kn = normalizar(k);
        if (q.indexOf(kn) !== -1 || kn.indexOf(q) !== -1) {
          if (kn.length > mejorPeso) { mejorPeso = kn.length; mejor = d; }
        }
      });
    });
    return mejor;
  }

  function responder(pregunta) {
    var q = normalizar(pregunta);
    if (/^(hola|buenas|hey|ola)\b/.test(q)) {
      return { texto: '¡Hola! ¿Qué sección del portal estás buscando?' };
    }
    if (q.indexOf('gracias') !== -1) {
      return { texto: '¡Con gusto! Acá ando si necesitas algo más. 🐿️' };
    }
    if (q.indexOf('cerrar sesion') !== -1 || q === 'salir') {
      return { texto: 'Te llevo a cerrar sesión…', redirigir: base + '/logout' };
    }
    var destino = buscarDestino(pregunta);
    if (destino) {
      return { texto: 'Te llevo a "' + destino.label + '"…', redirigir: destino.url };
    }
    return { texto: 'No encontré una sección exacta para eso. Prueba con otra palabra (por ejemplo "nómina", "presupuesto", "normatividad"), o revisa el menú lateral — ahí están todas las direcciones y recursos del portal.' };
  }

  // ——— Interfaz del chat ———
  var mensajes = document.getElementById('asistenteMensajes');
  var form = document.getElementById('asistenteForm');
  var input = document.getElementById('asistenteInput');
  var cerrar = document.getElementById('asistenteClose');
  var badge = document.getElementById('asistenteBadge');
  var abiertoAlgunaVez = false;

  function agregarMensaje(texto, tipo) {
    var div = document.createElement('div');
    div.className = 'asistente-msg ' + tipo;
    div.textContent = texto;
    mensajes.appendChild(div);
    mensajes.scrollTop = mensajes.scrollHeight;
  }

  function mostrarEscribiendo() {
    var div = document.createElement('div');
    div.className = 'asistente-typing';
    div.id = 'asistenteEscribiendo';
    div.innerHTML = '<span></span><span></span><span></span>';
    mensajes.appendChild(div);
    mensajes.scrollTop = mensajes.scrollHeight;
  }

  function quitarEscribiendo() {
    var el = document.getElementById('asistenteEscribiendo');
    if (el) el.remove();
  }

  function abrirPanel() {
    panel.hidden = false;
    if (badge) badge.style.display = 'none';
    if (!abiertoAlgunaVez) {
      abiertoAlgunaVez = true;
      var nombre = window.usuarioNombre;
      agregarMensaje(
        '¡Hola' + (nombre ? ', ' + nombre : '') + '! Soy Core 🐿️, tu asistente del Portal CORE. Pregúntame qué sección estás buscando (por ejemplo: "nómina", "presupuesto", "normatividad"...).',
        'bot'
      );
    }
    input.focus();
  }

  function cerrarPanel() {
    panel.hidden = true;
  }

  btn.addEventListener('click', function () {
    if (panel.hidden) abrirPanel(); else cerrarPanel();
  });
  if (cerrar) cerrar.addEventListener('click', cerrarPanel);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !panel.hidden) cerrarPanel();
  });
  // Respaldo: clic afuera del panel también lo cierra (por si el botón X
  // específico no responde en algún navegador/extensión).
  document.addEventListener('click', function (e) {
    if (panel.hidden) return;
    if (panel.contains(e.target) || btn.contains(e.target)) return;
    cerrarPanel();
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var texto = input.value.trim();
      if (!texto) return;
      agregarMensaje(texto, 'user');
      input.value = '';
      mostrarEscribiendo();
      setTimeout(function () {
        quitarEscribiendo();
        var r = responder(texto);
        agregarMensaje(r.texto, 'bot');
        // Coincidencia encontrada: redirige directo, como un acceso rápido
        // (deja ver el mensaje un momento antes de navegar).
        if (r.redirigir) {
          setTimeout(function () { window.location.href = r.redirigir; }, 700);
        }
      }, 450 + Math.random() * 400);
    });
  }
});
