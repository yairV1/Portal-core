<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/AdminController.php
//  Panel central del administrador global — reemplaza en el sidebar los 4
//  ítems sueltos que antes eran solo_admin=1 (Usuarios, Contenido landing,
//  Contrataciones, Postulaciones) por un único acceso ("Administración")
//  que reparte hacia esas 4 páginas, que siguen existiendo tal cual —
//  este archivo no las mueve ni las duplica, solo agrupa la entrada.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    mostrar_error(403);
    exit;
}

require ROOT_PATH . '/app/Views/Portal/Administracion/Administracion.php';
