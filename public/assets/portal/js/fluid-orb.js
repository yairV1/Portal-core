// fluid-orb.js — reemplaza el círculo estático (.doc-hero::after, ver
// centro-documental.css) por un orbe animado en WebGL, en los 6 módulos que
// comparten el hero del "centro documental" (Talento Humano, Administrativa
// y Financiera, Gestión Institucional, SGI, Vicerrectoría Académica,
// Investigación e Innovación).
//
// Puerto a JS vanilla (sin React/build step, este portal no los usa) de
// "Fluid Orb" — un WebGL orb con shading fluido animado, de la galería
// pública rare-ui (github.com/swamimalode07/rare-ui, componente
// "fluid-orb"): mismos shaders GLSL y misma lógica de animación que el
// original, solo el "wrapper" cambia (de un componente React con
// useRef/useEffect a una función plana que monta un <canvas> en un
// contenedor ya existente).
//
// El color se lee de --color-accent (paneles.css) para que el orbe se vea
// igual de bien en modo claro y oscuro sin tocar este archivo.
// Si WebGL no está disponible, no hace nada — el círculo CSS de siempre
// (::after) se queda como estaba, nunca un hueco vacío.

(function () {
  var VERT = [
    'attribute vec2 a_pos;',
    'void main() {',
    '  gl_Position = vec4(a_pos, 0.0, 1.0);',
    '}',
  ].join('\n');

  var FRAG = [
    '#ifdef GL_FRAGMENT_PRECISION_HIGH',
    'precision highp float;',
    '#else',
    'precision mediump float;',
    '#endif',
    'uniform vec2 u_resolution;',
    'uniform float u_time;',
    'uniform vec3 u_color;',
    'float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453123); }',
    'float noise(vec2 p) {',
    '  vec2 i = floor(p); vec2 f = fract(p); vec2 u = f * f * (3.0 - 2.0 * f);',
    '  return mix(mix(hash(i + vec2(0.0,0.0)), hash(i + vec2(1.0,0.0)), u.x),',
    '             mix(hash(i + vec2(0.0,1.0)), hash(i + vec2(1.0,1.0)), u.x), u.y);',
    '}',
    'float fbm(vec2 p) {',
    '  float v = 0.0; float a = 0.6;',
    '  for (int i = 0; i < 3; i++) { v += a * noise(p); p *= 2.0; a *= 0.5; }',
    '  return v;',
    '}',
    'void main() {',
    '  vec2 uv = gl_FragCoord.xy / u_resolution.xy;',
    '  float t = u_time * 0.22;',
    '  vec2 drift = vec2(sin(t) + 0.6 * sin(t * 1.7 + 1.3), cos(t * 0.8) + 0.6 * cos(t * 1.3 + 2.1));',
    '  vec2 p = vec2(uv.x * 1.8, uv.y * 1.0) + drift * 0.7;',
    '  vec2 q = vec2(fbm(p + drift), fbm(p + vec2(3.2, 1.5) - drift));',
    '  float f = fbm(p + 1.2 * q);',
    '  float g = clamp(1.0 - uv.y, 0.0, 1.0);',
    '  float anchor = smoothstep(0.0, 0.3, uv.y);',
    '  float shade = clamp(g + (f - 0.5) * 0.8 * anchor, 0.0, 1.0);',
    '  vec3 white = vec3(0.99, 1.0, 1.0);',
    '  vec3 light = mix(white, u_color, 0.5);',
    '  vec3 dark = u_color;',
    '  vec3 col = white;',
    '  col = mix(col, light, smoothstep(0.28, 0.52, shade));',
    '  col = mix(col, dark, smoothstep(0.58, 0.88, shade));',
    '  float edge = smoothstep(0.5, 0.49, distance(uv, vec2(0.5)));',
    '  gl_FragColor = vec4(col * edge, edge);',
    '}',
  ].join('\n');

  function hexToRgb(hex) {
    var h = (hex || '').replace('#', '').trim();
    if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
    var n = parseInt(h, 16);
    if (h.length !== 6 || isNaN(n)) return [0.62, 0.12, 0.39]; // fallback: --color-accent claro
    return [((n >> 16) & 255) / 255, ((n >> 8) & 255) / 255, (n & 255) / 255];
  }

  function compile(gl, type, src) {
    var shader = gl.createShader(type);
    if (!shader) return null;
    gl.shaderSource(shader, src);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
      gl.deleteShader(shader);
      return null;
    }
    return shader;
  }

  function mountFluidOrb(canvas, size, color) {
    var gl = canvas.getContext('webgl', { antialias: true, alpha: true });
    if (!gl) return null;

    var program = gl.createProgram();
    var vert = compile(gl, gl.VERTEX_SHADER, VERT);
    var frag = compile(gl, gl.FRAGMENT_SHADER, FRAG);
    if (!program || !vert || !frag) return null;

    gl.attachShader(program, vert);
    gl.attachShader(program, frag);
    gl.linkProgram(program);
    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return null;
    gl.useProgram(program);

    var buffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]), gl.STATIC_DRAW);
    var aPos = gl.getAttribLocation(program, 'a_pos');
    gl.enableVertexAttribArray(aPos);
    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);

    var uResolution = gl.getUniformLocation(program, 'u_resolution');
    var uTime = gl.getUniformLocation(program, 'u_time');
    var rgb = hexToRgb(color);
    gl.uniform3f(gl.getUniformLocation(program, 'u_color'), rgb[0], rgb[1], rgb[2]);

    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var px = Math.round(size * dpr);
    canvas.width = px;
    canvas.height = px;
    gl.viewport(0, 0, px, px);
    gl.uniform2f(uResolution, px, px);

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var start = performance.now();
    var raf = 0;

    function render(now) {
      gl.uniform1f(uTime, reduce ? 0 : (now - start) / 1000);
      gl.drawArrays(gl.TRIANGLES, 0, 6);
      if (!reduce) raf = requestAnimationFrame(render);
    }
    render(start);

    return function cleanup() {
      cancelAnimationFrame(raf);
      gl.deleteProgram(program);
      gl.deleteShader(vert);
      gl.deleteShader(frag);
      gl.deleteBuffer(buffer);
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    var heroes = document.querySelectorAll('.doc-hero');
    if (!heroes.length) return;

    var accent = getComputedStyle(document.body).getPropertyValue('--color-accent').trim() || '#9e1f63';
    var SIZE = 210;

    heroes.forEach(function (hero) {
      var wrap = document.createElement('div');
      wrap.className = 'doc-hero-orb';
      wrap.setAttribute('aria-hidden', 'true');
      var canvas = document.createElement('canvas');
      canvas.className = 'doc-hero-orb-canvas';
      wrap.appendChild(canvas);
      hero.appendChild(wrap);

      var cleanup = mountFluidOrb(canvas, SIZE, accent);
      if (cleanup) {
        hero.classList.add('doc-hero--orb'); // apaga el ::after CSS (ver centro-documental.css)
      } else {
        wrap.remove(); // sin WebGL: se queda el círculo CSS de siempre, no un canvas vacío
      }
    });
  });
})();
