<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/CarpetaDocumentalController.php
//  Acciones administrativas sobre carpetas_documentales (Gestión
//  Documental, ver PortalController.php '/gestion-documental' y migración
//  053_gestion_documental_publico.sql): alternar público/privado, renombrar
//  y "eliminar" (soft delete, activo = 0). No crea carpetas nuevas — sigue
//  siendo el árbol fijo de 2 niveles (dirección → área) que ya existía;
//  esto solo cura qué parte de ese árbol queda visible.
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Gestión Documental no tiene direccion_id (usuario_admin_de(null) exige
// admin global) — igual que /documentos/crear y /documentos/subir en
// DocumentoController.php para archivos_documentales.
if (!usuario_admin_de(null) || !usuario_puede_ver_archivo_de('gestion-documental')) {
    http_response_code(403);
    mostrar_error(403);
    exit;
}

function volver_carpeta_documental(?int $carpetaId, string $resultado): void
{
    $qs = '?carpeta_doc=' . urlencode($resultado);
    if ($carpetaId) {
        $qs .= '&carpeta=' . $carpetaId;
    }
    header('Location: ' . BASE_URL . '/gestion-documental' . $qs);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_carpeta_documental(null, 'error');
}
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    volver_carpeta_documental(null, 'error');
}

$carpetaId = (int) ($_POST['carpeta_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, parent_id, label, visibilidad FROM carpetas_documentales WHERE id = :id');
$stmt->execute([':id' => $carpetaId]);
$carpeta = $stmt->fetch();
if (!$carpeta) {
    volver_carpeta_documental(null, 'error');
}

// A dónde volver tras la acción: si es un "área" (tiene parent_id), a la
// vista de su "dirección" padre; si es una "dirección" (parent_id null), a
// la raíz del repositorio.
$volverACarpeta = $carpeta['parent_id'] ? (int) $carpeta['parent_id'] : null;

if ($uri === '/gestion-documental/carpetas/visibilidad') {
    $nuevaVisibilidad = $carpeta['visibilidad'] === 'publico' ? 'privado' : 'publico';
    $pdo->prepare('UPDATE carpetas_documentales SET visibilidad = :v WHERE id = :id')
        ->execute([':v' => $nuevaVisibilidad, ':id' => $carpetaId]);
    volver_carpeta_documental($volverACarpeta, 'visibilidad');
}

if ($uri === '/gestion-documental/carpetas/editar') {
    $nombreCarpeta = trim($_POST['nombre'] ?? '');
    if ($nombreCarpeta === '') {
        volver_carpeta_documental($volverACarpeta, 'nombre');
    }
    $pdo->prepare('UPDATE carpetas_documentales SET label = :label WHERE id = :id')
        ->execute([':label' => $nombreCarpeta, ':id' => $carpetaId]);
    volver_carpeta_documental($volverACarpeta, 'editado');
}

if ($uri === '/gestion-documental/carpetas/eliminar') {
    $pdo->prepare('UPDATE carpetas_documentales SET activo = 0 WHERE id = :id')->execute([':id' => $carpetaId]);
    volver_carpeta_documental($volverACarpeta, 'eliminado');
}

http_response_code(404);
mostrar_error(404);
