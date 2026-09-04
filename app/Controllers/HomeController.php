<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/HomeController.php
// ══════════════════════════════════════════════════════════

// Exige sesión activa — si no hay usuario logueado, manda al login
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$titulo = 'Inicio';

// TODO: Inicio.php sigue siendo un documento HTML completo (su propio
// <!DOCTYPE>/<html>/<head>) con datos de ejemplo hardcodeados en el <script>.
// Pendiente: 1) convertir Panel-Superior.html y Panel-Lateral-Izquierdo.html
// en fragmentos incluibles (igual que header.php/footer.php) e integrarlos
// aquí alrededor de Inicio.php; 2) reemplazar los arreglos de ejemplo
// (KPIS, ACCESOS, DOCS_RECIENTES...) por datos reales desde $pdo.
require ROOT_PATH . '/app/Views/Portal/Home/Inicio.php';