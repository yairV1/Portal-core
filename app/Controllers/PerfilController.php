<?php
// ══════════════════════════════════════════════════════════
//  app/Controllers/PerfilController.php
//  Edita nombre/foto del usuario en sesión — el formulario vive dentro
//  del panel de perfil compartido (ver portal-header.php).
//  "cargo" NO es autoeditable a propósito (antes sí lo era, ver migration
//  019/023): cualquiera podía ponerse a sí mismo un cargo falso tipo
//  "Dueño" — solo se cambia a mano en la base de datos hasta que exista
//  un panel de administración real (módulo de áreas y permisos).
//  $pdo, $csrf, $uri, e() ya vienen listos desde public/index.php
// ══════════════════════════════════════════════════════════

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Esta ruta no tiene vista propia — el formulario vive en el panel de
// perfil, disponible en cualquier página. Un GET directo no hace nada,
// solo vuelve al inicio.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ' . BASE_URL . '/?perfil=error');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    header('Location: ' . BASE_URL . '/?perfil=error');
    exit;
}

$foto = null; // solo se actualiza la columna si de verdad llega una imagen válida

if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['foto']['tmp_name'];

    // getimagesize() lee el contenido real del archivo — a diferencia del
    // nombre o el mimetype que manda el navegador, esos dos los puede
    // falsificar cualquiera con solo renombrar un archivo.
    $info = @getimagesize($tmp);
    $extensiones = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    if ($info === false || !isset($extensiones[$info[2]])) {
        header('Location: ' . BASE_URL . '/?perfil=formato');
        exit;
    }
    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) { // 2 MB
        header('Location: ' . BASE_URL . '/?perfil=tamano');
        exit;
    }

    // Nombre fijo, armado por el servidor a partir del id de sesión — nunca
    // del nombre que mande el navegador. Así no hay riesgo de path
    // traversal ni de pisar el archivo de otro usuario.
    $carpeta = ROOT_PATH . '/public/uploads/perfiles';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }
    $archivo = 'usuario_' . (int)$_SESSION['usuario_id'] . '.' . $extensiones[$info[2]];
    if (!move_uploaded_file($tmp, $carpeta . '/' . $archivo)) {
        header('Location: ' . BASE_URL . '/?perfil=error');
        exit;
    }
    $foto = '/uploads/perfiles/' . $archivo;
}

if ($foto !== null) {
    $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre, foto = :foto WHERE id = :id');
    $stmt->execute([':nombre' => $nombre, ':foto' => $foto, ':id' => $_SESSION['usuario_id']]);
    $_SESSION['usuario_foto'] = $foto;
} else {
    $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre WHERE id = :id');
    $stmt->execute([':nombre' => $nombre, ':id' => $_SESSION['usuario_id']]);
}

// Refresca la sesión ya mismo, para que el topbar y el cajón de perfil
// muestren el cambio sin pedir volver a iniciar sesión.
$_SESSION['usuario_nombre'] = $nombre;

header('Location: ' . BASE_URL . '/?perfil=1');
exit;
