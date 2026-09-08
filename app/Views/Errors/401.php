<?php
$codigo = 401;
$etiqueta = 'Acceso no autorizado';
$titulo = 'Necesitas iniciar sesión';
$mensaje = 'Tu sesión no está activa o ya venció. Inicia sesión para continuar.';
$enlace = BASE_URL . '/login';
$textoEnlace = 'Iniciar sesión';
require __DIR__ . '/_template.php';
