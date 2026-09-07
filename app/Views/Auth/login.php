<?php
// $csrf y $error ya vienen listos desde public/index.php / AuthController.php
// (mismo patrón que $pdo/$uri en los demás controladores) — no están
// "sin definir", el editor no puede rastrear el require que los trae.
$titulo = 'Iniciar sesión';
require ROOT_PATH . '/app/Views/layouts/header.php';
?>

<div class="auth-shell">
  <div class="slide-auth" id="slideAuth">

    <!-- ── Iniciar sesión ── -->
    <div class="form-container sign-in-container">
      <form method="POST" action="<?= BASE_URL ?>/login" class="slide-form" id="formLogin">
        <div class="slide-form-logo"><i class="fa-solid fa-right-to-bracket"></i></div>
        <h2>Iniciar sesión</h2>
        <p class="slide-form-sub">Portal CORE — COREDUCACIÓN</p>

        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="email" name="correo" required autofocus placeholder="Correo institucional">
        <input type="password" name="password" required placeholder="Contraseña">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Ingresar</button>

        <button type="button" class="slide-mobile-link" id="btnIrRecuperarMobile">¿Olvidaste tu contraseña?</button>
        <a href="<?= BASE_URL ?>/horario" class="slide-form-link">Consultar mi horario sin iniciar sesión</a>
      </form>
    </div>

    <!-- ── Recuperar contraseña ── -->
    <div class="form-container recover-container">
      <form class="slide-form" id="formRecuperar">
        <div class="slide-form-logo"><i class="fa-solid fa-key"></i></div>
        <h2>Recuperar contraseña</h2>
        <p class="slide-form-sub">Ingresa tu correo institucional. Por seguridad no hay restablecimiento automático: un administrador te contactará para verificar tu identidad.</p>
        <input type="email" id="recuperarCorreo" required placeholder="Correo institucional">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar solicitud</button>
        <button type="button" class="slide-mobile-link" id="btnIrLoginMobile">Volver a iniciar sesión</button>
      </form>

      <div class="slide-form" id="confirmRecuperar" hidden>
        <div class="slide-form-logo slide-form-logo-ok"><i class="fa-solid fa-check"></i></div>
        <h2>Solicitud recibida</h2>
        <p class="slide-form-sub">Un administrador se pondrá en contacto contigo para restablecer tu acceso.</p>
        <button type="button" class="btn btn-outline-primary" id="btnVolverLogin">Volver a iniciar sesión</button>
      </div>
    </div>

    <!-- ── Panel deslizante ── -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <div class="slide-form-logo slide-form-logo-ghost"><i class="fa-solid fa-right-to-bracket"></i></div>
          <h2>¿Ya la recuerdas?</h2>
          <p>Vuelve a la pantalla de inicio de sesión con tu correo y contraseña institucional.</p>
          <button type="button" class="btn btn-outline-light" id="btnIrLogin">Iniciar sesión</button>
        </div>
        <div class="overlay-panel overlay-right">
          <div class="slide-form-logo slide-form-logo-ghost"><i class="fa-solid fa-key"></i></div>
          <h2>¿Olvidaste tu contraseña?</h2>
          <p>Por seguridad no hay recuperación automática: solicítala y un administrador la restablecerá contigo.</p>
          <button type="button" class="btn btn-outline-light" id="btnIrRecuperar">Recuperar contraseña</button>
        </div>
      </div>
    </div>

    <!-- ── Overlay de carga tras enviar el login ── -->
    <div class="slide-loading" id="slideLoading" hidden>
      <div class="slide-loading-spinner"></div>
      <p>Redirigiendo a tu panel…</p>
    </div>

  </div>
</div>

<script>
  (function () {
    var container = document.getElementById('slideAuth');
    var formRecuperar = document.getElementById('formRecuperar');
    var confirmRecuperar = document.getElementById('confirmRecuperar');

    function mostrarRecuperar() { container.classList.add('right-panel-active'); }
    function mostrarLogin() {
      container.classList.remove('right-panel-active');
      // Espera a que termine el deslizamiento antes de resetear el formulario
      // de recuperación, para no ver el cambio de "confirmación" a "formulario"
      // a medio camino de la animación.
      setTimeout(function () {
        formRecuperar.hidden = false;
        confirmRecuperar.hidden = true;
        formRecuperar.reset();
      }, 650);
    }

    ['btnIrRecuperar', 'btnIrRecuperarMobile'].forEach(function (id) {
      var btn = document.getElementById(id);
      if (btn) btn.addEventListener('click', mostrarRecuperar);
    });
    ['btnIrLogin', 'btnIrLoginMobile', 'btnVolverLogin'].forEach(function (id) {
      var btn = document.getElementById(id);
      if (btn) btn.addEventListener('click', mostrarLogin);
    });

    // Sin backend de recuperación real (a propósito, ver docs/política de
    // seguridad arriba): solo confirma que la solicitud "llegó" y remite a
    // un administrador, tal como decía el aviso que reemplaza esta pantalla.
    if (formRecuperar) {
      formRecuperar.addEventListener('submit', function (e) {
        e.preventDefault();
        formRecuperar.hidden = true;
        confirmRecuperar.hidden = false;
      });
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
        slideLoading.hidden = false;
        var btn = formLogin.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;
        setTimeout(function () { formLogin.submit(); }, 800);
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
        text: <?= json_encode($error) ?>
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
