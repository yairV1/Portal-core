-- 049_contrataciones_nav_item.sql
-- "Contrataciones" (ContratacionController.php) se agregó al checklist de
-- Permisos por rol en la migración anterior (config/modulos.php, clave
-- 'contrataciones') pero nunca tuvo fila en nav_items — a diferencia de
-- Hojas de vida/Contratos/Certificaciones (migración 048), que sí la
-- tienen. Sin nav_item, PermisosController.php resuelve $modulo['id']=0
-- para esta clave y Permisos.php se salta la casilla de "Permisos por
-- cargo" (columnas SuperAdmin/Talento Humano salían vacías, sin casilla
-- que marcar ni desmarcar — bug real reportado 2026-09-21).
--
-- Se agrega como hijo de Talento Humano (mismo patrón que 048): además de
-- arreglar el checklist de cargo, esto la suma al submenú del sidebar, en
-- vez de depender solo del botón agregado a mano en Tal_Humano.php — mismo
-- criterio de "no fingir navegabilidad" ya aplicado en el resto del
-- proyecto (mejor un enlace real de menú que solo un botón suelto).

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-contrataciones', '/contrataciones', 'Contrataciones', 4
FROM nav_items WHERE slug = 'talento';
