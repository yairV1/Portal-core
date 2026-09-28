<?php
/**
 * Landing pública de Portal Core. El contenido institucional llega desde
 * las tablas landing_*; aquí solo permanece la estructura visual.
 */
$landing = static function (string $clave, string $campo) use ($landingSecciones): string {
    return e($landingSecciones[$clave][$campo] ?? '');
};
// Si todavía no se ha cargado contenido desde "Contenido landing", la
// portada no muestra encabezados ni cajas en blanco: cada bloque se pinta
// solo si tiene texto (el título del hero cae al nombre del producto).
$hay = static fn (string $clave, string $campo): bool => trim($landingSecciones[$clave][$campo] ?? '') !== '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $hay('hero', 'titulo') ? $landing('hero', 'titulo') : 'Portal CORE' ?> — COREDUCACIÓN</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/favicon-core.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= v('/assets/core/tokens.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
</head>
<body>
<nav class="navbar">
  <div class="container nav-row">
    <a href="#inicio" class="brand"><img src="<?= BASE_URL ?>/uploads/logo/Core-logo-black-removebg-preview.png" alt="COREDUCACIÓN" class="brand-logo-full"></a>
    <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="navPrincipal" aria-label="Abrir menú"><span></span><span></span><span></span></button>
    <nav class="links" id="navPrincipal" aria-label="Navegación principal">
      <a href="#modulos">Módulos</a><a href="#roles">Para tu rol</a><a href="#pasos">Cómo empiezas</a>
      <a href="<?= BASE_URL ?>/quienes-somos">Quiénes somos</a>
      <a href="<?= BASE_URL ?>/trabaja-con-nosotros">Trabaja con nosotros</a>
    </nav>
    <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-dark">Iniciar sesión</a>
  </div>
</nav>

<section class="hero" id="inicio">
  <div class="hero-content">
    <?php if ($hay('hero', 'etiqueta')): ?><span class="hero-kicker"><?= $landing('hero', 'etiqueta') ?></span><?php endif; ?>
    <h1 class="hero-giant"><?= $hay('hero', 'titulo') ? $landing('hero', 'titulo') : 'Portal CORE' ?></h1>
    <?php if ($hay('hero', 'descripcion')): ?><p class="hero-desc"><?= $landing('hero', 'descripcion') ?></p><?php endif; ?>
    <div class="hero-ctas">
      <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      <a href="#modulos" class="btn-pill btn-pill-outline">Ver módulos</a>
    </div>
  </div>
</section>

<?php
$featuresConTexto = array_filter(['seguridad', 'conexion', 'medida'], fn ($sec) => $hay($sec, 'titulo'));
if ($hay('por_que', 'titulo') || $landingEstadisticas || $featuresConTexto):
?>
<section class="why">
  <div class="container">
    <div class="why-grid">
      <div class="why-copy">
        <?php if ($hay('por_que', 'etiqueta')): ?><span class="hero-kicker why-kicker"><?= $landing('por_que', 'etiqueta') ?></span><?php endif; ?>
        <?php if ($hay('por_que', 'titulo')): ?><h2 class="mt-2"><?= $landing('por_que', 'titulo') ?></h2><?php endif; ?>
        <?php if ($hay('por_que', 'descripcion')): ?><p><?= $landing('por_que', 'descripcion') ?></p><?php endif; ?>
        <div class="stat-row">
          <?php foreach ($landingEstadisticas as $estadistica): ?>
            <div class="stat-item">
              <div class="stat-icon"><i class="bi bi-<?= e($estadistica['icono']) ?>" aria-hidden="true"></i></div>
              <div><strong><?= e($estadistica['valor']) ?></strong><span><?= e($estadistica['descripcion']) ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ($featuresConTexto): ?>
      <div class="feature-panel">
        <?php foreach ($featuresConTexto as $seccion): ?>
          <div class="feature-card">
            <div class="icon" aria-hidden="true"><i class="bi bi-info-circle" aria-hidden="true"></i></div>
            <div><h3><?= $landing($seccion, 'titulo') ?></h3><p><?= $landing($seccion, 'descripcion') ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($hay('modulos', 'titulo') || $landingModulos): ?>
<section class="modules" id="modulos">
  <div class="modules-head">
    <div><?php if ($hay('modulos', 'titulo')): ?><h2><?= $landing('modulos', 'titulo') ?></h2><?php endif; ?></div>
    <?php if ($hay('modulos', 'descripcion')): ?><p><?= $landing('modulos', 'descripcion') ?></p><?php endif; ?>
  </div>
  <?php if ($landingModulos): ?>
  <div class="module-carousel">
    <div class="module-track" id="module-track">
      <?php foreach ($landingModulos as $modulo): ?>
        <div class="module-card">
          <div class="module-visual module-visual-<?= e($modulo['tema']) ?>">
            <span class="module-price-badge"><?= e($modulo['estado']) ?></span>
            <i class="bi bi-<?= e($modulo['icono']) ?>" aria-hidden="true"></i>
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
      <button type="button" id="scroll-left" aria-label="Módulos anteriores">‹</button>
      <button type="button" id="scroll-right" aria-label="Módulos siguientes">›</button>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($hay('roles', 'titulo') || $landingRoles): ?>
<section class="roles" id="roles">
  <div class="container">
    <div class="roles-grid">
      <div class="role-intro">
        <div><?php if ($hay('roles', 'titulo')): ?><h2 class="role-intro-titulo"><?= $landing('roles', 'titulo') ?></h2><?php endif; ?><?php if ($hay('roles', 'descripcion')): ?><p><?= $landing('roles', 'descripcion') ?></p><?php endif; ?></div>
        <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-white">Iniciar sesión</a>
      </div>
      <?php foreach ($landingRoles as $rol): ?>
        <div class="role-card role-card-<?= e($rol['tema']) ?>">
          <span class="role-badge" aria-hidden="true"><i class="bi bi-<?= e($rol['icono']) ?>" aria-hidden="true"></i></span>
          <div><h3><?= e($rol['titulo']) ?></h3><p><?= e($rol['descripcion']) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($hay('pasos', 'titulo') || $landingPasos): ?>
<section class="steps" id="pasos">
  <div class="container">
    <?php if ($hay('pasos', 'titulo')): ?><h2><?= $landing('pasos', 'titulo') ?></h2><?php endif; ?>
    <div class="steps-row">
      <?php foreach ($landingPasos as $paso): ?>
        <div class="step">
          <div class="step-circle" aria-hidden="true"><i class="bi bi-<?= e($paso['icono']) ?>" aria-hidden="true"></i></div>
          <h3><?= e($paso['titulo']) ?></h3>
          <p><?= e($paso['descripcion']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<script src="<?= v('/assets/web/js/nav.js') ?>"></script>
<script src="<?= v('/assets/web/js/inicio.js') ?>"></script>
</body>
</html>
