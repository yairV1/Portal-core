-- "Permisos por rol" — panel de Administración donde el admin global puede
-- quitarle a admin_direccion/usuario el acceso a módulos puntuales del
-- sidebar (ver PermisosController.php). El admin global nunca se restringe
-- a sí mismo (ni aparece en el checklist) — solo estos otros dos roles.
--
-- Modelo "solo excepciones": una fila = acceso DENEGADO a ese nav_item para
-- ese rol. Sin fila = permitido (comportamiento actual, sin cambios, hasta
-- que alguien lo restrinja a propósito) — así ningún despliegue nuevo de
-- esta tabla vacía cambia nada por sí solo.
CREATE TABLE IF NOT EXISTS permisos_rol_negados (
  nav_item_id INT NOT NULL,
  rol ENUM('admin_direccion', 'usuario') NOT NULL,
  PRIMARY KEY (nav_item_id, rol),
  CONSTRAINT permisos_rol_negados_nav_item_fk
    FOREIGN KEY (nav_item_id) REFERENCES nav_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sidebar: "Permisos por rol" dentro de la sección Administración (ver
-- Modulos.php, que ya la muestra por código para rol=admin, sin necesidad
-- de nav_item propio porque esa sección no viene de la BD como el resto
-- del sidebar). No hace falta fila en nav_secciones/nav_items: el acceso a
-- /permisos-por-rol lo protege PermisosController.php directamente (mismo
-- criterio que /administracion con AdminController.php).
