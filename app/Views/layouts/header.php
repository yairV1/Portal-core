<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="theme-color" content="#9E1F63">
<script>
  // Si el navegador restaura esta página desde su caché de "atrás/adelante"
  // (bfcache), la vuelve a pedir al servidor en vez de mostrarla tal cual
  // quedó pintada — evita, por ejemplo, volver acá y ver el overlay de
  // "Redirigiendo a tu panel…" congelado de un envío anterior del login.
  window.addEventListener('pageshow', function (evento) {
    if (evento.persisted) window.location.reload();
  });
</script>
<title><?= isset($titulo) ? e($titulo) . ' - ' : '' ?>Control de Salones AC</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/logo/logo-core.jpg">

<!-- PWA: permite instalar el sistema en el celular (Agregar a inicio) -->
<link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Salones AC">
<meta name="mobile-web-app-capable" content="yes">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/login/style.css" rel="stylesheet">
</head>
<body>