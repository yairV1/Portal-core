    </div><!-- /.content-inner -->
  </main><!-- /.content -->
</div><!-- /.app-shell -->

<!-- ── Asistente flotante "Core" (ver asistente.js) ── -->
<button type="button" class="asistente-fab" id="btnAsistente" aria-label="Abrir el asistente del portal">
  <img src="<?= BASE_URL ?>/uploads/mascota/core-avatar.png" alt="Core">
  <span class="badge-nuevo" id="asistenteBadge"></span>
</button>
<div class="asistente-panel" id="asistentePanel" hidden>
  <div class="asistente-header">
    <span class="asistente-avatar"><img src="<?= BASE_URL ?>/uploads/mascota/core-avatar.png" alt="Core"></span>
    <div>
      <strong>Core</strong>
      <small>Asistente del Portal</small>
    </div>
    <button type="button" class="asistente-close" id="asistenteClose" aria-label="Cerrar asistente"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  </div>
  <div class="asistente-mensajes" id="asistenteMensajes"></div>
  <form class="asistente-form" id="asistenteForm">
    <input type="text" id="asistenteInput" placeholder="¿Qué estás buscando?" autocomplete="off" aria-label="Pregúntale a Core">
    <button type="submit" aria-label="Enviar"><i class="bi bi-send" aria-hidden="true"></i></button>
  </form>
</div>
<script>
  window.BASE_URL = <?= json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  window.usuarioNombre = <?= json_encode(!empty($nombre) && $nombre !== 'Invitado' ? explode(' ', trim($nombre))[0] : '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php
// ── Avisos tras una acción (toasts) ──
// Cada controlador vuelve con ?grupo=resultado (ej. ?usuarios=creado, ver
// UsuariosController.php) y acá se traduce a un aviso. Antes eran 12
// bloques <script> casi idénticos con SweetAlert2; ahora es un solo mapa y
// el componente de toast del sistema de diseño (UI.toast en core/ui.js).
// Mismos grupos, mismos resultados y mismos textos que antes. Si llega un
// resultado desconocido, se usa el 'error' de ese grupo.
$AVISOS = [
    'perfil' => [3000, [
        '1'       => ['success', 'Perfil actualizado'],
        'formato' => ['error', 'La foto debe ser JPG, PNG o WEBP'],
        'tamano'  => ['error', 'La foto pesa más de 2 MB'],
        'error'   => ['error', 'No se pudo guardar el perfil'],
    ]],
    'usuarios' => [3500, [
        'creado'                => ['success', 'Usuario creado'],
        'actualizado'           => ['success', 'Usuario actualizado'],
        'eliminado'             => ['success', 'Usuario eliminado'],
        'datos'                 => ['error', 'Revisa el correo y que la contraseña tenga al menos 8 caracteres'],
        'direccion'             => ['error', 'Falta elegir la dirección para ese rol'],
        'correo_existente'      => ['error', 'Ya existe un usuario con ese correo'],
        'auto_rol'              => ['error', 'No puedes quitarte a ti mismo el rol de administrador global'],
        'auto_eliminar'         => ['error', 'No puedes eliminar tu propio usuario'],
        'ultimo_admin_rol'      => ['error', 'No se pudo cambiar el rol: es el único administrador global que queda'],
        'ultimo_admin_eliminar' => ['error', 'No se pudo eliminar: es el único administrador global que queda'],
        'error'                 => ['error', 'No se pudo completar la acción'],
    ]],
    'doc' => [3000, [
        '1'           => ['success', 'Documento subido correctamente'],
        'creado'      => ['success', 'Documento creado — ya puedes subirle el archivo'],
        'editado'     => ['success', 'Documento actualizado'],
        'visibilidad' => ['success', 'Visibilidad del documento actualizada'],
        'eliminado'   => ['success', 'Documento eliminado'],
        'nombre'      => ['error', 'Escribe un nombre y una fecha válida'],
        'formato'     => ['error', 'El archivo debe ser PDF, Word, Excel o PowerPoint'],
        'tamano'      => ['error', 'El archivo pesa más de 20 MB'],
        'duplicado'   => ['error', 'Este empleado ya tiene un documento de este tipo — elimínalo antes de subir otro'],
        'error'       => ['error', 'No se pudo subir el documento'],
    ]],
    'empleado' => [3500, [
        'creado'           => ['success', 'Empleado agregado'],
        'actualizado'      => ['success', 'Empleado actualizado'],
        'eliminado'        => ['success', 'Empleado eliminado'],
        'datos'            => ['error', 'Revisa el nombre, el documento y la fecha'],
        'documento_existe' => ['error', 'Ya existe un empleado con ese número de documento'],
        'error'            => ['error', 'No se pudo completar la acción'],
    ]],
    'drive' => [3000, [
        '1'                    => ['success', 'Documento subido correctamente'],
        'carpeta'              => ['success', 'Carpeta creada'],
        'eliminado'            => ['success', 'Documento eliminado'],
        'carpeta_eliminada'    => ['success', 'Carpeta eliminada'],
        'nombre'               => ['error', 'Escribe un nombre de carpeta válido'],
        'formato'              => ['error', 'El archivo debe ser PDF, Word, Excel, PowerPoint o una imagen (JPG/PNG/WEBP)'],
        'tamano'               => ['error', 'El archivo pesa más de 15 MB'],
        'importado'            => ['success', 'Documento importado desde Drive'],
        'drive_link'           => ['error', 'Pega un link válido de Google Drive'],
        'drive_no_encontrado'  => ['error', 'No se pudo acceder a ese archivo — revisa el link y que esté compartido con la cuenta de servicio'],
        'drive_google_doc'     => ['error', 'Ese es un Doc/Sheet/Slide nativo de Google — expórtalo primero como PDF o Word desde Drive'],
        'drive_no_configurado' => ['error', 'La importación desde Drive todavía no está configurada en este entorno'],
        'error'                => ['error', 'No se pudo completar la acción'],
    ]],
    'carpeta_doc' => [3000, [
        'visibilidad' => ['success', 'Visibilidad de la carpeta actualizada'],
        'editado'     => ['success', 'Carpeta renombrada'],
        'eliminado'   => ['success', 'Carpeta eliminada'],
        'nombre'      => ['error', 'Escribe un nombre de carpeta válido'],
        'error'       => ['error', 'No se pudo completar la acción'],
    ]],
    'drive_personal' => [3500, [
        'conectado'      => ['success', 'Tu Google Drive quedó conectado'],
        'desconectado'   => ['success', 'Desconectaste tu Google Drive'],
        'importado'      => ['success', 'Documento importado desde tu Drive'],
        'no_configurado' => ['error', 'Conectar Drive todavía no está configurado en este entorno'],
        'sin_refresh'    => ['error', 'Google no autorizó el acceso — intenta conectar de nuevo'],
        'no_conectado'   => ['error', 'Conecta tu Google Drive primero'],
        'token_vencido'  => ['error', 'Tu conexión con Drive venció — conéctala de nuevo'],
        'no_encontrado'  => ['error', 'No se pudo acceder a ese archivo'],
        'formato'        => ['error', 'Ese archivo debe ser PDF, Word, Excel o PowerPoint (o un Doc/Sheet/Slide de Google)'],
        'tamano'         => ['error', 'El archivo pesa más de 15 MB'],
        'error'          => ['error', 'No se pudo completar la acción'],
    ]],
    'evento' => [3000, [
        '1'         => ['success', 'Evento creado correctamente'],
        'editado'   => ['success', 'Evento actualizado correctamente'],
        'eliminado' => ['success', 'Evento eliminado'],
        'error'     => ['error', 'No se pudo guardar el evento'],
    ]],
    'landing' => [3000, [
        'guardado'  => ['success', 'Guardado correctamente'],
        'eliminado' => ['success', 'Eliminado correctamente'],
        'error'     => ['error', 'No se pudo guardar — revisa los campos obligatorios'],
    ]],
    'soporte' => [3000, [
        '1'         => ['success', 'Soporte agregado'],
        'eliminado' => ['success', 'Soporte eliminado'],
        'error'     => ['error', 'No se pudo guardar el soporte'],
    ]],
    'pendiente' => [3000, [
        '1'         => ['success', 'Pendiente agregado'],
        'eliminado' => ['success', 'Pendiente eliminado'],
        'error'     => ['error', 'No se pudo guardar el pendiente'],
    ]],
];
$toasts = [];
if (!empty($_GET['bienvenida'])) {
    // AuthController agrega ?bienvenida=1 tras un login correcto.
    $primerNombre = (!empty($nombre) && $nombre !== 'Invitado') ? explode(' ', trim($nombre))[0] : '';
    $toasts[] = [
        'type' => 'success',
        'title' => '¡Bienvenido' . ($primerNombre !== '' ? ', ' . $primerNombre : '') . '!',
        'text' => 'Core está lista para ayudarte a encontrar lo que necesites.',
        'image' => BASE_URL . '/uploads/mascota/core-avatar.png',
        'duration' => 3200,
    ];
}
foreach ($AVISOS as $grupo => [$duracion, $mensajes]) {
    if (!isset($_GET[$grupo]) || !is_string($_GET[$grupo])) continue;
    [$tipo, $texto] = $mensajes[$_GET[$grupo]] ?? $mensajes['error'];
    $toasts[] = ['type' => $tipo, 'title' => $texto, 'duration' => $duracion];
}
?>
<script src="<?= v('/assets/core/ui.js') ?>"></script>
<?php if ($toasts): ?>
<script>
  (<?= json_encode($toasts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>).forEach(function (t, i) {
    setTimeout(function () { UI.toast(t); }, i * 150);
  });
</script>
<?php endif; ?>

<script src="<?= v('/assets/layouts/js/paneles.js') ?>"></script>
<script src="<?= v('/assets/layouts/js/asistente.js') ?>"></script>
<script src="<?= v('/assets/portal/js/fluid-orb.js') ?>"></script>
</body>
</html>
