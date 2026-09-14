<?php
$codigoVista = str_ends_with($uri, '/403') ? 403 : 500;
http_response_code($codigoVista);
require ROOT_PATH . '/app/Views/Errors/' . $codigoVista . '.php';
