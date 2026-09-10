<?php
/**
 * Landing pública "Formulario de contratación" — acceso SIEMPRE por
 * token (nunca listado). $yaCompletado/$expirado/$reciente/$errorEnvio y
 * $TIPOS_DOCUMENTO/$CAMPOS_TEXTO vienen de ContratacionController.php.
 * $csrf/BASE_URL/e()/v() ya vienen listos desde public/index.php.
 *
 * El formulario se ve en 3 pasos (barra de progreso + GSAP para la
 * transición), pero sigue siendo un solo <form> con un solo POST al
 * final — los pasos ocultos igual mandan sus campos, ContratacionController.php
 * no cambia por esto.
 */
$pasosForm = [
    1 => [
        'titulo' => 'Datos personales',
        'grupos' => null, // paso especial: los 14 campos de texto, no documentos
    ],
    2 => [
        'titulo' => 'Documentos de identificación',
        'grupos' => [
            'Hojas de vida'                => ['hv_coreducacion', 'hv_normal'],
            'Identificación y tributario'  => ['doc_cedula', 'tarjeta_profesional', 'rut'],
        ],
    ],
    3 => [
        'titulo' => 'Certificados y seguridad social',
        'grupos' => [
            'Seguridad social'    => ['cert_seguridad_social', 'cert_pension', 'cert_arl', 'cert_cuenta_bancaria'],
            'Antecedentes'        => ['cert_procuraduria', 'cert_policia', 'cert_contraloria', 'cert_rnmc', 'ruaf'],
            'Académico y laboral' => ['diploma_posgrado', 'cert_laboral'],
            'Salud y otros'       => ['carne_vacunas', 'otros'],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulario de contratación — Portal Core COREDUCACIÓN</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/favicon-core.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= v('/assets/web/css/inicio.css') ?>">
<link rel="stylesheet" href="<?= v('/assets/web/css/contratacion.css') ?>">
</head>
<body>

<nav class="navbar">
  <div class="container nav-row">
    <a href="<?= BASE_URL ?>/" class="brand">
      <img src="<?= BASE_URL ?>/uploads/logo/Core-logo-black-removebg-preview.png" alt="COREDUCACIÓN" class="brand-logo-full">
    </a>
  </div>
</nav>

<section class="hero" style="min-height:220px">
  <div class="hero-content">
    <span class="hero-kicker">Portal institucional de COREDUCACIÓN</span>
    <h1 class="ct-titulo">Formulario de contratación</h1>
  </div>
</section>

<section class="ct-pasos">
  <div class="container">
    <div class="ct-pasos-row">
      <div class="ct-paso ct-paso-hecho"><span class="ct-paso-num">1</span><span>Postulación</span></div>
      <div class="ct-paso ct-paso-hecho"><span class="ct-paso-num">2</span><span>Entrevista</span></div>
      <div class="ct-paso ct-paso-hecho"><span class="ct-paso-num">3</span><span>Microclase</span></div>
      <div class="ct-paso ct-paso-actual"><span class="ct-paso-num">4</span><span>Contratación</span></div>
    </div>
  </div>
</section>

<div class="container">

<?php if ($yaCompletado && $reciente): ?>
  <div class="ct-aviso ct-aviso-ok">
    <i class="bi bi-check-circle-fill"></i>
    <div>
      <strong>¡Gracias! Tu información quedó registrada.</strong>
      <p>El equipo de Gestión Humana revisará tus documentos y se pondrá en contacto contigo para los siguientes pasos.</p>
    </div>
  </div>

<?php elseif ($yaCompletado): ?>
  <div class="ct-aviso ct-aviso-info">
    <i class="bi bi-info-circle-fill"></i>
    <div>
      <strong>Esta postulación ya fue enviada.</strong>
      <p>Este enlace ya se usó. Si necesitas corregir algo de tu información, comunícate directamente con Gestión Humana.</p>
    </div>
  </div>

<?php elseif ($expirado): ?>
  <div class="ct-aviso ct-aviso-info">
    <i class="bi bi-clock-history"></i>
    <div>
      <strong>Este enlace ya expiró.</strong>
      <p>Solicita uno nuevo a Gestión Humana para continuar con tu proceso de contratación.</p>
    </div>
  </div>

<?php else: ?>

  <?php if ($errorEnvio): ?>
    <?php
      $mensajesError = [
        'tamano'  => 'Alguno de los archivos pesa más de 5 MB.',
        'formato' => 'Alguno de los archivos no es un PDF ni una imagen real (JPG/PNG/WEBP).',
        '1'       => 'Revisa que todos los campos y documentos obligatorios estén completos.',
      ];
    ?>
    <div class="ct-aviso ct-aviso-error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div><?= e($mensajesError[$errorEnvio] ?? $mensajesError['1']) ?></div>
    </div>
  <?php endif; ?>

  <!-- Barra de progreso del formulario (interna, no confundir con las 4
       etapas del proceso de arriba) — calendario.js-style: nada de esto
       toca el backend, solo muestra/oculta pasos del mismo <form>. -->
  <div class="ct-progreso">
    <div class="ct-progreso-texto">Paso <span id="ctPasoActual">1</span> de <?= count($pasosForm) ?>: <span id="ctPasoTitulo"><?= e($pasosForm[1]['titulo']) ?></span></div>
    <div class="ct-progreso-barra"><div class="ct-progreso-fill" id="ctProgresoFill"></div></div>
  </div>

  <form action="<?= BASE_URL ?>/contratacion/enviar" method="post" enctype="multipart/form-data" class="ct-form" id="ctForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <?php foreach ($pasosForm as $numPaso => $paso): ?>
      <div class="ct-step" data-paso="<?= $numPaso ?>" <?= $numPaso !== 1 ? 'hidden' : '' ?>>

        <?php if ($paso['grupos'] === null): ?>
          <h2 class="ct-seccion-titulo">Datos personales</h2>
          <div class="ct-grid">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="text" name="cedula" placeholder="Cédula" required>
            <input type="tel" name="celular" placeholder="Celular" required>
            <input type="email" name="email" placeholder="Correo" required>
            <input type="text" name="estado_civil" placeholder="Estado civil" required>
            <input type="text" name="direccion" placeholder="Dirección de residencia" required>
            <input type="text" name="profesion" placeholder="Profesión" required>
            <input type="text" name="ciudad" placeholder="Ciudad" required>
            <input type="text" name="cuenta_bancaria" placeholder="Cuenta bancaria (CTA)" required>
            <input type="text" name="nivel_academico" placeholder="Nivel académico" required>
            <input type="text" name="eps" placeholder="EPS (Salud)" required>
            <input type="text" name="fondo_pension" placeholder="Fondo de pensión" required>
            <input type="text" name="arl" placeholder="ARL" required>
            <input type="text" name="fondo_cesantias" placeholder="Fondo de cesantías" required>
          </div>
        <?php else: ?>
          <?php foreach ($paso['grupos'] as $grupoTitulo => $claves): ?>
            <h2 class="ct-seccion-titulo"><?= e($grupoTitulo) ?></h2>
            <div class="ct-grid ct-grid-docs">
              <?php foreach ($claves as $clave): $info = $TIPOS_DOCUMENTO[$clave]; ?>
                <label class="ct-file">
                  <span><?= e($info['label']) ?><?= $info['requerido'] ? '' : ' (opcional)' ?></span>
                  <input type="file" name="<?= e($clave) ?>" accept=".pdf,.jpg,.jpeg,.png,.webp" <?= $info['requerido'] ? 'required' : '' ?>>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    <?php endforeach; ?>

    <div class="ct-nav">
      <button type="button" class="btn-pill btn-pill-outline-dark" id="ctBtnAnterior" hidden>Anterior</button>
      <button type="button" class="btn-pill btn-pill-dark" id="ctBtnSiguiente">Siguiente</button>
      <button type="submit" class="btn-pill btn-pill-dark" id="ctBtnEnviar" hidden>Enviar información</button>
    </div>
  </form>

<?php endif; ?>

</div>
<div style="height:70px"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha384-g4NTh/Iv5PPU4xPyhEWqPcwtNXOvdaDI8LLnyYfyNZOjKJeYQyjzQ9X5275eBjpt" crossorigin="anonymous"></script>
<script>window.CT_TOTAL_PASOS = <?= count($pasosForm) ?>;</script>
<script>window.CT_TITULOS_PASOS = <?= json_encode(array_column($pasosForm, 'titulo'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
<script src="<?= v('/assets/web/js/contratacion.js') ?>"></script>
</body>
</html>
