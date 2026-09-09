(function () {
  'use strict';

  /* ── Fondo animado del hero: blobs de gradiente en Canvas 2D, sin
     librería — el blur real lo da el CSS (filter:blur en .qs-canvas), acá
     solo se mueven círculos de color lento en el tiempo. ── */
  var canvas = document.getElementById('qsCanvas');
  if (canvas && canvas.getContext) {
    var ctx = canvas.getContext('2d');
    var blobs = [
      { color: '#9E1F63', rx: .28, ry: .35, sx: .00035, sy: .00028, r: .38 },
      { color: '#F15A29', rx: .68, ry: .28, sx: .00030, sy: .00040, r: .34 },
      { color: '#6E1747', rx: .45, ry: .70, sx: .00022, sy: .00033, r: .40 },
    ];

    function resize() {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    function frame(t) {
      var w = canvas.width, h = canvas.height;
      ctx.clearRect(0, 0, w, h);
      blobs.forEach(function (b) {
        var x = (b.rx + Math.sin(t * b.sx) * .12) * w;
        var y = (b.ry + Math.cos(t * b.sy) * .12) * h;
        var radius = b.r * Math.max(w, h);
        var grad = ctx.createRadialGradient(x, y, 0, x, y, radius);
        grad.addColorStop(0, b.color);
        grad.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);
      });
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  /* ── Tilt 3D ligero al mover el mouse sobre una tarjeta ── */
  document.querySelectorAll('.qs-tilt').forEach(function (wrap) {
    var card = wrap.firstElementChild;
    if (!card) return;
    wrap.addEventListener('mousemove', function (e) {
      var rect = wrap.getBoundingClientRect();
      var px = (e.clientX - rect.left) / rect.width - .5;
      var py = (e.clientY - rect.top) / rect.height - .5;
      card.style.transform = 'rotateY(' + (px * 8) + 'deg) rotateX(' + (py * -8) + 'deg) translateY(-4px)';
    });
    wrap.addEventListener('mouseleave', function () {
      card.style.transform = 'rotateY(0) rotateX(0) translateY(0)';
    });
  });

  /* ── Reveal al hacer scroll (GSAP + ScrollTrigger) ── */
  if (window.gsap && window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);

    gsap.to('.qs-fade', { opacity: 1, y: 0, duration: .8, stagger: .12, ease: 'power2.out', delay: .1 });

    gsap.utils.toArray('.qs-reveal').forEach(function (el, i) {
      gsap.to(el, {
        opacity: 1, y: 0, duration: .6, ease: 'power2.out',
        delay: (i % 4) * .06,
        scrollTrigger: { trigger: el, start: 'top 90%' },
      });
    });
  } else {
    // Si GSAP no cargó (ej. sin internet), que el contenido se vea igual.
    document.querySelectorAll('.qs-fade, .qs-reveal').forEach(function (el) {
      el.style.opacity = 1;
      el.style.transform = 'none';
    });
  }

  // Segunda red de seguridad, sin importar si GSAP corrió o no: un
  // .qs-reveal usa scrollTrigger (arranca en opacity:0 hasta que ese
  // elemento cruza el 90% del viewport), así que si el usuario nunca
  // baja hasta ahí, o el layout cambia de alto después de que
  // ScrollTrigger ya calculó las posiciones (ej. las fuentes web de
  // Google tardan en cargar y corren el texto), puede quedar invisible
  // para siempre aunque GSAP sí haya cargado bien. No reemplaza la
  // animación de arriba — solo garantiza que a los ~1.5s el contenido
  // esté visible de todas formas, la haya disparado GSAP o no.
  setTimeout(function () {
    document.querySelectorAll('.qs-fade, .qs-reveal').forEach(function (el) {
      el.style.opacity = 1;
      el.style.transform = 'none';
    });
  }, 1500);
})();
