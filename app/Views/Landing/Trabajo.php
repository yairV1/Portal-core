<?php
/**
 * Landing pública "Trabaja con nosotros" — sin sesión. $vacantes viene de
 * TrabajoController.php (tabla vacantes, no quemadas acá). $csrf/BASE_URL/
 * e()/v() ya vienen listos desde public/index.php.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trabaja con nosotros — Portal Core COREDUCACIÓN</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/favicon-core.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/web/css/trabajo.css') ?>">
</head>
<body>

<nav class="navbar">
  <div class="container nav-row">
    <a href="<?= BASE_URL ?>/" class="brand">
      <img src="<?= BASE_URL ?>/uploads/logo/Core-logo-black-removebg-preview.png" alt="COREDUCACIÓN" class="brand-logo-full">
    </a>
    <nav class="links" aria-label="Navegación principal">
      <a href="<?= BASE_URL ?>/">Inicio</a>
      <a href="#vacantes">Vacantes</a>
      <a href="#postular">Postularme</a>
    </nav>
    <a href="<?= BASE_URL ?>/login" class="btn-pill btn-pill-dark">Iniciar sesión</a>
  </div>
</nav>

<section class="hero" style="min-height:360px">
  <div class="hero-content">
    <span class="hero-kicker">Portal institucional de COREDUCACIÓN</span>
    <div class="hero-giant" style="font-size:clamp(2.2rem,7vw,4.2rem)">Trabaja con<br>nosotros</div>
    <p class="hero-desc">COREDUCACIÓN es una institución educativa con sede en Honda, Tolima. Buscamos personas comprometidas con la formación de calidad — estas son nuestras vacantes abiertas hoy.</p>
    <div class="hero-ctas">
      <a href="#vacantes" class="btn-pill btn-pill-white">Ver vacantes</a>
      <a href="#postular" class="btn-pill btn-pill-outline">Postularme</a>
    </div>
  </div>
</section>

<?php if (isset($_GET['postulacion'])): ?>
  <?php
    $avisos = [
      '1'      => ['ok',    '¡Postulación enviada! Gracias por tu interés — te contactaremos si tu perfil encaja con la vacante.'],
      'error'  => ['error', 'Revisa los datos del formulario: falta algo o algún archivo no llegó completo.'],
      'formato'=> ['error', 'La hoja de vida debe ser un PDF real y la foto un JPG/PNG/WEBP real.'],
      'tamano' => ['error', 'La hoja de vida no puede pesar más de 5 MB, ni la foto más de 2 MB.'],
      'limite' => ['error', 'Ya enviaste varias postulaciones en la última hora. Intenta de nuevo más tarde.'],
    ];
    $aviso = $avisos[$_GET['postulacion']] ?? $avisos['error'];
  ?>
  <div class="container">
    <div class="aviso-postulacion aviso-<?= e($aviso[0]) ?>"><?= e($aviso[1]) ?></div>
  </div>
<?php endif; ?>

<section class="vacantes" id="vacantes">
  <div class="container">
    <div class="modules-head">
      <div><h2>Vacantes abiertas</h2></div>
      <p>Cargos disponibles hoy en COREDUCACIÓN — la lista se actualiza según necesidad institucional.</p>
    </div>

    <?php if (!$vacantes): ?>
      <p style="color:var(--text-soft)">No hay vacantes abiertas por el momento. Vuelve a revisar pronto.</p>
    <?php else: ?>
      <div class="vacantes-grid">
        <?php foreach ($vacantes as $v): ?>
          <div class="vacante-card">
            <span class="vacante-tag"><?= e($v['tipo_contrato']) ?></span>
            <h3><?= e($v['titulo']) ?></h3>
            <p class="vacante-area"><?= e($v['area']) ?></p>
            <p class="vacante-desc"><?= e($v['descripcion']) ?></p>
            <button type="button" class="btn-pill btn-pill-dark btn-postular" data-id="<?= (int) $v['id'] ?>" data-titulo="<?= e($v['titulo']) ?>">
              Postularme a este cargo
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="postular" id="postular">
  <div class="container">
    <div class="modules-head">
      <div><h2>Envía tu postulación</h2></div>
      <p>Completa tus datos y adjunta tu hoja de vida — te contactaremos si tu perfil encaja con alguna vacante.</p>
    </div>

    <form action="<?= BASE_URL ?>/trabaja-con-nosotros/postular" method="post" enctype="multipart/form-data" class="form-postular" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="vacante_id" id="vacanteId" value="">

      <!-- Honeypot: invisible para una persona (ver trabajo.css .hp-field),
           un bot que autocompleta todo sí lo llena. Si llega con
           contenido, TrabajoController.php descarta el envío en silencio. -->
      <div class="hp-field" aria-hidden="true">
        <label for="sitioWeb">No completar este campo</label>
        <input type="text" name="sitio_web" id="sitioWeb" tabindex="-1" autocomplete="off">
      </div>

      <div class="form-grid">
        <input type="text" name="nombre" placeholder="Nombre completo" maxlength="150" required>
        <input type="email" name="correo" placeholder="Correo" maxlength="150" required>
        <input type="tel" name="telefono" placeholder="Teléfono" maxlength="30" required>
        <input type="text" name="cargo_aplicado" id="cargoAplicado" placeholder="Cargo al que aplicas" maxlength="150" required>
      </div>

      <div class="form-grid">
        <label class="form-file">
          <span>Hoja de vida (PDF, máx. 5 MB)</span>
          <input type="file" name="hoja_vida" accept=".pdf" required>
        </label>
        <label class="form-file">
          <span>Foto (JPG/PNG/WEBP, máx. 2 MB)</span>
          <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" required>
        </label>
      </div>

      <button type="submit" class="btn-pill btn-pill-dark">Enviar postulación</button>
    </form>
  </div>
</section>

<script src="<?= v('/assets/web/js/trabajo.js') ?>"></script>
</body>
</html>
