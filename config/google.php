<?php
// Client ID de Google OAuth — el MISMO que habilita el botón "Continuar con
// Google" (ver AuthController.php) y "Mi Google Drive" en Gestión
// Documental (ver DriveUsuarioController.php).
//
// El Client ID es PÚBLICO: viaja en el HTML del botón de Google Identity
// Services, eso es normal y esperado. Lo que NUNCA se pone acá (ni en el
// HTML, ni en el repo) es el Client secret — este flujo por ID token no lo
// usa; el secret solo lo consume el intercambio servidor-a-servidor del
// Drive personal.
//
// Se resuelve igual que la config de BD (ver config/database.php): primero
// variable de entorno, después config/google.local.php (gitignoreado, uno
// por máquina). Si no hay ninguno de los dos, queda vacío → el botón avisa
// que Google no está configurado y el resto del portal sigue igual.

$configLocal = is_file(__DIR__ . '/google.local.php')
    ? require __DIR__ . '/google.local.php'
    : [];

return [
    'client_id'     => trim((string) (getenv('GOOGLE_CLIENT_ID') ?: ($configLocal['client_id'] ?? ''))),
    // Solo lo necesita "Mi Google Drive" (intercambio servidor-a-servidor,
    // ver GoogleDrive.php). El login por ID token NO lo usa — por eso puede
    // quedar vacío sin afectar el botón "Continuar con Google".
    'client_secret' => trim((string) (getenv('GOOGLE_CLIENT_SECRET') ?: ($configLocal['client_secret'] ?? ''))),
];
