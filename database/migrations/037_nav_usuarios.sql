-- Ítem de sidebar para el nuevo Panel de Usuarios (ver
-- UsuariosController.php) — solo_admin=1: sidebar.php ya filtra
-- solo_admin por ($_SESSION['usuario_rol'] === 'admin'), así que un
-- admin_direccion (ver migración 036_roles_por_direccion.sql) no lo ve,
-- solo el admin global.
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, solo_admin, orden)
VALUES (3, NULL, 'usuarios', '/usuarios', 'Usuarios', 'people-fill', 1, 9);
