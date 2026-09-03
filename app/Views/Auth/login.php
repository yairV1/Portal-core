<?php $titulo = 'Iniciar sesión'; require ROOT_PATH . '/app/Views/layouts/header.php'; ?>

<div class="auth-split">
  <div class="auth-split-brand">
    <div class="auth-logo mb-3" style="background: rgba(255,255,255,.16);">
      <i class="fa-solid fa-snowflake"></i>
    </div>
    <h2 class="fw-bold mb-2">Control de Salones AC</h2>
    <p class="mb-1 opacity-75">COREDUCACIÓN — Patrimonio Educativo Regional</p>
    <p class="opacity-75" style="max-width:420px;">
      Panel de administración, apoyo operativo y consulta docente para el uso
      de salones y salas de sistemas con aire acondicionado.
    </p>
    <ul class="list-unstyled mt-4 opacity-75 small">
      <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i> Dashboard con estado en tiempo real</li>
      <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i> Reportes y estadísticas de uso</li>
      <li class="mb-2"><i class="fa-solid fa-circle-check me-2"></i> Historial exportable a CSV</li>
    </ul>
  </div>

  <div class="auth-split-form">
    <div class="auth-card">
      <div class="text-center mb-4">
        <div class="auth-logo"><i class="fa-solid fa-right-to-bracket"></i></div>
        <h4 class="mb-0">Iniciar sesión</h4>
        <small class="text-muted">Ingresa con tu cuenta institucional</small>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/login">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="mb-3">
          <label class="form-label">Correo institucional</label>
          <input type="email" name="correo" class="form-control form-control-lg" required autofocus placeholder="nombre@coreducacion.edu.co">
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control form-control-lg" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">
          <i class="fa-solid fa-right-to-bracket"></i> Ingresar
        </button>
      </form>

      <div class="text-center mt-4 small text-muted">
        <i class="fa-solid fa-lock"></i> ¿Olvidaste tu contraseña? Solicítala a un administrador —
        por seguridad, no hay recuperación automática.
      </div>

      <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/horario" class="btn btn-outline-secondary btn-sm w-100">
          <i class="fa-solid fa-magnifying-glass"></i> Consultar mi horario de clase (sin iniciar sesión)
        </a>
      </div>
    </div>
  </div>
</div>

<?php require ROOT_PATH . '/app/Views/layouts/footer.php'; ?>