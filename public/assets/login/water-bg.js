/* ============================================================
   Portal CORE — fondo de agua interactiva del login.
   Canvas 2D puro (sin WebGL ni librerías): un degradado de marca fijo
   + líneas onduladas que se distorsionan cerca del mouse y anillos de
   onda que se disparan al mover el cursor (y solos, de vez en cuando,
   para que la pantalla nunca se vea "vacía" en monitores grandes).
   ============================================================ */
(function () {
  'use strict';

  var canvas = document.getElementById('authWaterCanvas');
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext('2d');

  var reduceMotion = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var W = 0, H = 0;

  function resize() {
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = Math.round(W * dpr);
    canvas.height = Math.round(H * dpr);
    canvas.style.width = W + 'px';
    canvas.style.height = H + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  window.addEventListener('resize', resize);
  resize();

  var mouse = { x: W / 2, y: H / 2, active: false };
  var ripples = [];
  var lastSpawn = 0;

  function addRipple(x, y, strength) {
    ripples.push({ x: x, y: y, age: 0, strength: strength || 1 });
    if (ripples.length > 10) ripples.shift();
  }

  window.addEventListener('pointermove', function (e) {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.active = true;
    var now = performance.now();
    if (now - lastSpawn > 110) {
      addRipple(mouse.x, mouse.y, 0.55);
      lastSpawn = now;
    }
  }, { passive: true });

  window.addEventListener('pointerleave', function () { mouse.active = false; });

  // Ondas ambientales: aunque nadie toque el mouse, cada tanto nace una
  // onda suave en un punto aleatorio — la pantalla respira sola.
  var ambientTimer = null;
  function spawnAmbient() {
    addRipple(Math.random() * W, Math.random() * H * 0.8, 0.8);
    ambientTimer = window.setTimeout(spawnAmbient, 3400 + Math.random() * 2600);
  }

  // Bandas horizontales: cuántas puntos por línea depende del ancho de
  // pantalla, con un tope, para que un monitor ultra-wide no dispare el
  // costo de dibujo.
  var LINE_SPACING = 48;
  var STEP = 14;

  function draw(time) {
    ctx.clearRect(0, 0, W, H);

    var grad = ctx.createLinearGradient(0, 0, W, H);
    grad.addColorStop(0, '#150a24');
    grad.addColorStop(0.55, '#7a1850');
    grad.addColorStop(1, '#c9660f');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, W, H);

    ctx.lineWidth = 1;
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.07)';
    for (var y = -LINE_SPACING; y < H + LINE_SPACING; y += LINE_SPACING) {
      ctx.beginPath();
      for (var x = 0; x <= W; x += STEP) {
        var ambient = Math.sin(x * 0.01 + time * 0.00055 + y * 0.02) * 4;
        var yOff = y + ambient;

        if (mouse.active) {
          var dx = x - mouse.x, dy = y - mouse.y;
          var dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 420) {
            yOff += Math.sin(dist * 0.02 - time * 0.0025) * (1 - dist / 420) * 16;
          }
        }
        if (x === 0) ctx.moveTo(x, yOff); else ctx.lineTo(x, yOff);
      }
      ctx.stroke();
    }

    for (var i = 0; i < ripples.length; i++) {
      var r = ripples[i];
      r.age += 16;
      var radius = r.age * 0.32;
      var alpha = Math.max(0, 1 - r.age / 1900) * 0.32 * r.strength;
      if (alpha <= 0) continue;
      ctx.beginPath();
      ctx.arc(r.x, r.y, radius, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(255, 255, 255, ' + alpha.toFixed(3) + ')';
      ctx.lineWidth = 2;
      ctx.stroke();
    }
    ripples = ripples.filter(function (r) { return r.age < 1900; });

    if (!reduceMotion) window.requestAnimationFrame(draw);
  }

  if (reduceMotion) {
    draw(0);
  } else {
    spawnAmbient();
    window.requestAnimationFrame(draw);
  }
})();
