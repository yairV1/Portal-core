<?php
// ══════════════════════════════════════════════════════════
//  app/Helpers/Trello.php
//  Lectura del tablero de Trello configurado — UNA cuenta compartida
//  (igual que GoogleDrive.php con su cuenta de servicio), no una por
//  usuario. Sin SDK: cURL directo a la API REST de Trello, que se
//  autentica con key+token en la query string (no hay JWT/OAuth de por
//  medio, es más simple que Google Drive).
//
//  Requiere TRELLO_API_KEY, TRELLO_TOKEN y TRELLO_BOARD_ID (ver
//  .env.example). Sin esas variables, trello_configurado() da false y
//  quien la usa decide qué mensaje mostrar (ver PortalController.php).
// ══════════════════════════════════════════════════════════

function trello_configurado(): bool
{
    foreach (['TRELLO_API_KEY', 'TRELLO_TOKEN', 'TRELLO_BOARD_ID'] as $variable) {
        $valor = getenv($variable);
        if ($valor === false || $valor === '') {
            return false;
        }
    }
    return true;
}

// Listas del tablero con sus tarjetas abiertas (no archivadas). "?cards=open"
// en el endpoint de listas ya trae las tarjetas anidadas en una sola
// llamada, en vez de un N+1 de una petición por lista. Devuelve null si el
// tablero no está configurado o la API no responde 200 (token revocado,
// tablero borrado, etc.) — quien la usa decide qué mensaje mostrar.
function trello_listas_con_tarjetas(): ?array
{
    if (!trello_configurado()) {
        return null;
    }

    $query = http_build_query([
        'key'         => getenv('TRELLO_API_KEY'),
        'token'       => getenv('TRELLO_TOKEN'),
        'cards'       => 'open',
        'card_fields' => 'name,due,shortUrl',
        'fields'      => 'name',
    ]);
    $ch = curl_init('https://api.trello.com/1/boards/' . rawurlencode(getenv('TRELLO_BOARD_ID')) . '/lists?' . $query);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($codigo !== 200) {
        return null;
    }

    $listas = json_decode((string) $respuesta, true);
    if (!is_array($listas)) {
        return null;
    }

    return array_map(fn ($lista) => [
        'nombre'   => $lista['name'],
        'tarjetas' => array_map(fn ($tarjeta) => [
            'nombre'      => $tarjeta['name'],
            'vencimiento' => trello_formatear_fecha($tarjeta['due'] ?? null),
            'url'         => $tarjeta['shortUrl'],
        ], $lista['cards'] ?? []),
    ], $listas);
}

// La fecha de vencimiento llega tal cual la devuelve la API de Trello (una
// fuente externa que este portal no controla) — si algún día cambia de
// formato o llega corrupta, DateTime lanza una excepción; se atrapa acá
// para que una tarjeta con fecha rara solo pierda su fecha, no tumbe toda
// la página (ver app/Views/Portal/Trello/Trello.php).
function trello_formatear_fecha(?string $fecha): ?string
{
    if (!$fecha) {
        return null;
    }
    try {
        return (new DateTime($fecha))->format('d/m/Y');
    } catch (Exception $e) {
        return null;
    }
}
