<?php
// $csrf y $error ya vienen listos desde public/index.php / AuthController.php
// (mismo patrón que $pdo/$uri en los demás controladores) — no están
// "sin definir", el editor no puede rastrear el require que los trae.
$titulo = 'Iniciar sesión';

// La mascota "Core" es opcional: si el archivo (recorte transparente, sin
// fondo propio) todavía no está en public/assets/login/img/, el panel se ve
// bien igual con solo el fondo líquido — nunca un ícono de imagen rota.
$mascotaSrc = null;
foreach (['core-mascota.webp', 'core-mascota.png'] as $candidato) {
    if (is_file(ROOT_PATH . '/public/assets/login/img/' . $candidato)) {
        $mascotaSrc = '/assets/login/img/' . $candidato;
        break;
    }
}

require ROOT_PATH . '/app/Views/layouts/header.php';
?>

<div class="auth-shell">
  <!-- ── Agua interactiva de fondo (toda la pantalla, detrás de la tarjeta):
       canvas + JS vanilla, sin librerías 3D. Reacciona al mouse y también
       ondula sola de a poco para que la pantalla nunca se vea "vacía" en
       monitores grandes. Ver water-bg.js. ── -->
  <canvas id="authWaterCanvas" class="auth-water-canvas" aria-hidden="true"></canvas>

  <div class="core-auth-card" id="coreAuthCard">

    <!-- ── Panel visual: cristal esmerilado que deja ver el agua de fondo,
         con la mascota Core (si existe el archivo) y el texto superpuesto. ── -->
    <div class="core-auth-visual" aria-hidden="true">
      <div class="core-auth-visual-pattern"></div>
      <?php if ($mascotaSrc): ?>
        <img src="<?= BASE_URL . $mascotaSrc ?>" alt="" class="core-auth-mascot" decoding="async" fetchpriority="high">
      <?php endif; ?>
      <div class="core-auth-visual-copy">
        <img src="<?= BASE_URL ?>/uploads/logo/logo-core.png" alt="" class="core-auth-visual-mark">
        <span class="core-auth-kicker">PORTAL CORE</span>
        <h2 class="core-auth-tagline">Tu ecosistema<br>educativo, en un solo lugar.</h2>
        <p class="core-auth-visual-sub">Gestión académica, documental y administrativa de COREDUCACIÓN.</p>
      </div>
    </div>

    <!-- ── Panel de formulario ── -->
    <div class="core-auth-form-panel">
      <form method="POST" action="<?= BASE_URL ?>/login" class="core-auth-form" id="formLogin" novalidate>
        <a href="<?= BASE_URL ?>/" class="core-auth-logo"><img src="<?= BASE_URL ?>/uploads/logo/logo-core.png" alt="Portal CORE"></a>
        <h1>Iniciar sesión</h1>
        <p class="core-auth-form-sub">Ingresa con tu correo institucional para continuar.</p>

        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

        <div class="core-auth-field">
          <label for="loginCorreo">Correo electrónico</label>
          <input type="email" name="correo" id="loginCorreo" required autofocus autocomplete="username" placeholder="tu.correo@coreducacion.edu.co" aria-describedby="errCorreo">
          <span class="field-error" id="errCorreo" role="alert" hidden></span>
        </div>

        <div class="core-auth-field">
          <label for="loginPassword">Contraseña</label>
          <div class="input-with-action">
            <input type="password" name="password" id="loginPassword" required autocomplete="current-password" placeholder="Tu contraseña" aria-describedby="errPassword">
            <button type="button" class="input-toggle-visibility" id="btnTogglePass" aria-label="Mostrar contraseña">
              <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </button>
          </div>
          <span class="field-error" id="errPassword" role="alert" hidden></span>
        </div>

        <button type="submit" class="btn btn-primary core-auth-submit" id="btnLoginSubmit">
          <span class="btn-label"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Ingresar</span>
          <span class="btn-loading" hidden><span class="btn-spinner" aria-hidden="true"></span> Ingresando…</span>
        </button>

        <div class="core-auth-divider" role="separator"><span>o</span></div>

        <button type="button" class="core-auth-google" id="btnGoogleLogin">
          <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
            <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/>
            <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.36 0-4.36-1.6-5.08-3.74H.9v2.33A8.997 8.997 0 0 0 9 18z"/>
            <path fill="#FBBC05" d="M3.92 10.68A5.4 5.4 0 0 1 3.64 9c0-.58.1-1.15.28-1.68V4.99H.9A8.997 8.997 0 0 0 0 9c0 1.45.35 2.83.9 4.01l3.02-2.33z"/>
            <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A8.997 8.997 0 0 0 .9 4.99l3.02 2.33C4.64 5.18 6.64 3.58 9 3.58z"/>
          </svg>
          <span>Continuar con Google</span>
        </button>

        <a href="<?= BASE_URL ?>/horario" class="core-auth-link">Consultar mi horario sin iniciar sesión</a>
      </form>
    </div>

    <!-- ── Overlay de carga tras enviar el login ── -->
    <div class="slide-loading" id="slideLoading" hidden>
      <div class="slide-loading-spinner"></div>
      <p>Redirigiendo a tu panel…</p>
    </div>

  </div>
</div>

<script src="<?= BASE_URL ?>/assets/login/water-bg.js" defer></script>
<script>
  (function () {
    // Mostrar/ocultar contraseña — botón real (no solo ícono decorativo),
    // con aria-label que refleja la acción disponible, no el estado actual.
    var loginPassword = document.getElementById('loginPassword');
    var btnTogglePass = document.getElementById('btnTogglePass');
    if (loginPassword && btnTogglePass) {
      btnTogglePass.addEventListener('click', function () {
        var oculta = loginPassword.type === 'password';
        loginPassword.type = oculta ? 'text' : 'password';
        btnTogglePass.setAttribute('aria-label', oculta ? 'Ocultar contraseña' : 'Mostrar contraseña');
        btnTogglePass.querySelector('i').className = oculta ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
      });
    }

    // Validación inline del login: en vez de dejar que el navegador muestre
    // su bocadillo nativo (o un alert genérico), el error aparece debajo del
    // campo que falló. El form usa novalidate para desactivar la validación
    // nativa y tomar el control acá.
    var loginCorreo = document.getElementById('loginCorreo');
    var errCorreo = document.getElementById('errCorreo');
    var errPassword = document.getElementById('errPassword');

    function mostrarErrorCampo(input, errEl, mensaje) {
      input.classList.add('field-invalid');
      input.setAttribute('aria-invalid', 'true');
      errEl.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> ' + mensaje;
      errEl.hidden = false;
    }
    function limpiarErrorCampo(input, errEl) {
      input.classList.remove('field-invalid');
      input.removeAttribute('aria-invalid');
      errEl.hidden = true;
    }
    if (loginCorreo && errCorreo) {
      loginCorreo.addEventListener('input', function () { limpiarErrorCampo(loginCorreo, errCorreo); });
    }
    if (loginPassword && errPassword) {
      loginPassword.addEventListener('input', function () { limpiarErrorCampo(loginPassword, errPassword); });
    }

    function validarLogin() {
      var valido = true;
      if (!loginCorreo.value.trim()) {
        mostrarErrorCampo(loginCorreo, errCorreo, 'Ingresa tu correo institucional.');
        valido = false;
      } else if (!loginCorreo.checkValidity()) {
        mostrarErrorCampo(loginCorreo, errCorreo, 'Ese correo no parece válido.');
        valido = false;
      } else {
        limpiarErrorCampo(loginCorreo, errCorreo);
      }
      if (!loginPassword.value) {
        mostrarErrorCampo(loginPassword, errPassword, 'Ingresa tu contraseña.');
        valido = false;
      } else {
        limpiarErrorCampo(loginPassword, errPassword);
      }
      return valido;
    }

    // Overlay de carga al enviar el login. En local (o cualquier conexión
    // rápida) el servidor responde casi al instante y el spinner apenas
    // alcanza a verse, así que acá SÍ se frena el envío un momento
    // (preventDefault + submit real después) — tiempo mínimo para que el
    // overlay se note, se sume al tiempo real que tarde el servidor.
    var formLogin = document.getElementById('formLogin');
    var slideLoading = document.getElementById('slideLoading');
    if (formLogin && slideLoading) {
      formLogin.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validarLogin()) {
          var primerInvalido = formLogin.querySelector('.field-invalid');
          if (primerInvalido) primerInvalido.focus();
          return;
        }
        var btn = document.getElementById('btnLoginSubmit');
        if (btn) {
          btn.disabled = true;
          var label = btn.querySelector('.btn-label');
          var loading = btn.querySelector('.btn-loading');
          if (label) label.hidden = true;
          if (loading) loading.hidden = false;
        }
        slideLoading.hidden = false;
        setTimeout(function () { formLogin.submit(); }, 800);
      });
    }

    // Google todavía no tiene un backend de OAuth propio en este proyecto
    // (no hay credenciales/cliente configurados) — en vez de simular un
    // login falso, se avisa con el mismo patrón de aviso que ya usa el
    // resto del sistema, para no dejar el botón sin respuesta.
    var btnGoogleLogin = document.getElementById('btnGoogleLogin');
    if (btnGoogleLogin) {
      btnGoogleLogin.addEventListener('click', function () {
        if (typeof SwalBrand === 'undefined') return;
        SwalBrand.fire({
          icon: 'info',
          title: 'Muy pronto',
          text: 'El inicio de sesión con Google institucional está en preparación. Por ahora, ingresa con tu correo y contraseña.'
        });
      });
    }
  })();

  // Avisos con SweetAlert2 (SwalBrand se define en footer.php, cargado
  // después de este bloque — por eso se espera a DOMContentLoaded, que no
  // dispara hasta que TODO el HTML, incluido footer.php, ya se ejecutó).
  window.addEventListener('DOMContentLoaded', function () {
    if (typeof SwalBrand === 'undefined') return;
    <?php if (!empty($error)): ?>
      SwalBrand.fire({
        icon: 'error',
        title: 'No se pudo iniciar sesión',
        text: <?= json_encode($error, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
      });
    <?php elseif (!empty($_GET['salida'])): ?>
      // toast: true → aviso pequeño en la esquina, sin fondo oscurecido ni
      // modal encima de la tarjeta de login (antes se tapaban entre sí).
      SwalBrand.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Sesión cerrada',
        text: 'Vuelve cuando quieras.',
        timer: 3200,
        timerProgressBar: true,
        showConfirmButton: false
      });
    <?php elseif (!empty($_GET['expirada'])): ?>
      SwalBrand.fire({
        icon: 'warning',
        title: 'Tu sesión expiró',
        text: 'Cerramos tu sesión por inactividad. Inicia sesión de nuevo para continuar.'
      });
    <?php endif; ?>
  });
</script>

<?php require ROOT_PATH . '/app/Views/layouts/footer.php'; ?>
