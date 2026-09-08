<?php
$codigo = 500;
$etiqueta = 'Error del servidor';
$titulo = 'Algo no salió como esperábamos';
$mensaje = 'El sistema encontró un problema interno. Inténtalo de nuevo en unos momentos.';
$enlace = BASE_URL . '/login';
$textoEnlace = 'Volver al inicio de sesión';
require __DIR__ . '/_template.php';
