<?php
/**
 * Landing pública de Portal Core. El contenido institucional llega desde
 * las tablas landing_*; aquí solo permanece la estructura visual.
 */
$landing = static function (string $clave, string $campo) use ($landingSecciones): string {
    return e($landingSecciones[$clave][$campo] ?? '');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $landing('hero', 'titulo') ?> — COREDUCACIÓN</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/favicon-core.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
</head>
<body>
<nav class="navbar">
  <div class="container nav-row">
    <a href="#inicio" class="brand"><img src="<?= BASE_URL ?>/uploads/logo/Core-logo-black-removebg-preview.png" alt="COREDUCACIÓN" class="brand-logo-full"></a>
    <nav class="links" aria-label="Navegación principal">
      <a href="#modulos">Módulos</a><a href="#roles">Para tu rol</a><a href="#pasos">Cómo empiezas</a>
      <a href="<?= BASE_URL ?>/quienes-somos">Quiénes somos</a>
      <a href="<?= BASE_URL ?>/trabaja-con-nosotros">Trabaja con nosotros</a>
    </nav>
    <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-dark">Iniciar sesión</a>
  </div>
</nav>

<section class="hero" id="inicio">
  <div class="hero-content">
    <span class="hero-kicker"><?= $landing('hero', 'etiqueta') ?></span>
    <div class="hero-giant"><?= $landing('hero', 'titulo') ?></div>
    <p class="hero-desc"><?= $landing('hero', 'descripcion') ?></p>
    <div class="hero-ctas">
      <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      <a href="#modulos" class="btn-pill btn-pill-outline">Ver módulos</a>
    </div>
  </div>
</section>

<section class="why">
  <div class="container">
    <div class="row" style="display:grid; grid-template-columns:1.1fr .9fr; gap:56px; align-items:start;">
      <div class="why-copy">
        <span class="hero-kicker" style="color:var(--magenta-dark);"><?= $landing('por_que', 'etiqueta') ?></span>
        <h2 class="mt-2"><?= $landing('por_que', 'titulo') ?></h2>
        <p><?= $landing('por_que', 'descripcion') ?></p>
        <div class="stat-row">
          <?php foreach ($landingEstadisticas as $estadistica): ?>
            <div class="stat-item">
              <div class="stat-icon"><i class="bi bi-<?= e($estadistica['icono']) ?>"></i></div>
              <div><strong><?= e($estadistica['valor']) ?></strong><span><?= e($estadistica['descripcion']) ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="feature-panel">
        <?php foreach (['seguridad', 'conexion', 'medida'] as $seccion): ?>
          <div class="feature-card">
            <div class="icon"><i class="bi bi-info-circle"></i></div>
            <div><h3><?= $landing($seccion, 'titulo') ?></h3><p><?= $landing($seccion, 'descripcion') ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="modules" id="modulos">
  <div class="modules-head">
    <div><h2><?= $landing('modulos', 'titulo') ?></h2></div>
    <p><?= $landing('modulos', 'descripcion') ?></p>
  </div>
  <div class="module-carousel">
    <div class="module-track" id="module-track">
      <?php foreach ($landingModulos as $modulo): ?>
        <div class="module-card">
          <div class="module-visual module-visual-<?= e($modulo['tema']) ?>">
            <span class="module-price-badge"><?= e($modulo['estado']) ?></span>
            <i class="bi bi-<?= e($modulo['icono']) ?>"></i>
          </div>
          <div class="module-body">
            <h3><?= e($modulo['titulo']) ?></h3>
            <p class="meta"><?= e($modulo['meta']) ?></p>
            <p class="loc">📍 <?= e($modulo['ubicacion']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="modules-foot">
    <div class="carousel-dots" id="carousel-dots"></div>
    <div class="carousel-arrows">
      <button id="scroll-left" aria-label="Módulos anteriores">‹</button>
      <button id="scroll-right" aria-label="Módulos siguientes">›</button>
    </div>
  </div>
</section>

<section class="roles" id="roles">
  <div class="container">
    <div class="roles-grid">
      <div class="role-intro">
        <div><h2 style="font-size:1.25rem;"><?= $landing('roles', 'titulo') ?></h2><p><?= $landing('roles', 'descripcion') ?></p></div>
        <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      </div>
      <?php foreach ($landingRoles as $rol): ?>
        <div class="role-card role-card-<?= e($rol['tema']) ?>">
          <span class="role-badge"><i class="bi bi-<?= e($rol['icono']) ?>"></i></span>
          <div><h3><?= e($rol['titulo']) ?></h3><p><?= e($rol['descripcion']) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="steps" id="pasos">
  <div class="container">
    <h2><?= $landing('pasos', 'titulo') ?></h2>
    <div class="steps-row">
      <?php foreach ($landingPasos as $paso): ?>
        <div class="step">
          <div class="step-circle"><i class="bi bi-<?= e($paso['icono']) ?>"></i></div>
          <h3><?= e($paso['titulo']) ?></h3>
          <p><?= e($paso['descripcion']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script src="<?= v('/assets/web/js/inicio.js') ?>"></script>
</body>
</html>
