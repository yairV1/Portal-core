<?php
// Conexión a la base de datos portal_core.
// Usa el usuario dedicado 'portal_user' (creado en MySQL), nunca 'root'.

$DB_HOST = 'localhost';
$DB_NAME = 'portal_core';
$DB_USER = 'portal_user';
$DB_PASS = 'Portal2026!'; // ajusta si pusiste otra contraseña al crear el usuario

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
