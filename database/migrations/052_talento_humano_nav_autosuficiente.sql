-- 052_talento_humano_nav_autosuficiente.sql
-- Hallazgo del code-review (2026-09-21): las migraciones 048 y 049
-- asumen que ya existe un nav_item con slug='talento' (creado en la
-- migración 035) para colgarle los 4 hijos nuevos (Hojas de vida/
-- Contratos/Certificaciones laborales/Contrataciones). Pero la migración
-- 039_nav_todos_los_modulos.sql — que ya existía antes de esta sesión —
-- BORRA justo esa fila (y la sección "Direcciones" completa) al
-- consolidar el sidebar en "Todos los módulos". Si algún día se aplican
-- las migraciones en orden estricto sobre un entorno nuevo (039 antes que
-- 048/049, que es el orden "correcto" por número), el `INSERT ... SELECT
-- ... WHERE slug='talento'` de 048/049 encuentra 0 filas y no inserta
-- nada — sin romper nada visible (las rutas siguen funcionando), pero sin
-- submenú en el sidebar y sin casilla de "Permisos por cargo" para esos 4
-- módulos.
--
-- Confirmado: en los dos entornos reales de este proyecto (Docker y
-- Apache nativo) la migración 039 NUNCA se aplicó — 'talento' existe en
-- ambos con datos correctos — así que esto no cambia nada en ninguno de
-- los dos hoy. Es una red de seguridad para cualquier entorno futuro,
-- para que 048/049 dejen de depender en silencio de qué otras migraciones
-- se hayan aplicado antes.

-- 0) El entorno nativo (portal-core.local) quedó SIN el UNIQUE KEY sobre
--    nav_items.slug que sí tiene Docker desde 000_esquema_base.sql —
--    deriva reintentar el INSERT IGNORE de un hijo ya existente (como el
--    paso 3 de abajo) duplica la fila en vez de ignorarla en silencio
--    (hallazgo real: pasó al probar esta misma migración). Se agrega acá
--    si falta, detectado por information_schema (mismo patrón ya usado en
--    045_permisos_rol_modulo.sql para tablas) — sin duplicar el índice si
--    ya existe (Docker, donde esto es un no-op).
SET @tiene_unique_slug = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'nav_items' AND index_name = 'slug'
);
SET @sql = IF(@tiene_unique_slug = 0,
  'ALTER TABLE nav_items ADD UNIQUE KEY slug (slug)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 1) Si la sección "Direcciones" no existe (ej. 039 sí se aplicó y la
--    borró), se recrea con el mismo mostrar_titulo=0 que tenía desde la
--    migración 005 — el orden exacto no importa (sidebar.php ordena por
--    la columna `orden`, y NULL/0 la deja simplemente antes de "Recursos").
INSERT INTO nav_secciones (label, mostrar_titulo, orden)
SELECT 'Direcciones', 0, 2
WHERE NOT EXISTS (SELECT 1 FROM nav_secciones WHERE label = 'Direcciones');

-- 2) Si el nav_item 'talento' no existe, se recrea con los mismos datos
--    que le dio la migración 035 (ruta/label/icono) — sin fijar un id
--    literal (los dos entornos reales ya tienen autoincrementales
--    distintos para esta fila, ver memoria del proyecto).
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden)
SELECT s.id, NULL, 'talento', '/talento-humano', 'Talento Humano', 'people', 5
FROM nav_secciones s WHERE s.label = 'Direcciones'
AND NOT EXISTS (SELECT 1 FROM nav_items WHERE slug = 'talento')
LIMIT 1;

-- 3) Vuelve a intentar los 4 hijos (idéntico a 048/049, INSERT IGNORE por
--    slug único — no duplica los que ya existen, solo crea los que
--    faltaban porque el padre no estaba disponible antes).
INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-hojas-de-vida', '/talento-humano/hojas-de-vida', 'Hojas de vida', 1
FROM nav_items WHERE slug = 'talento';

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-contratos', '/talento-humano/contratos', 'Contratos', 2
FROM nav_items WHERE slug = 'talento';

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-certificaciones-laborales', '/talento-humano/certificaciones-laborales', 'Certificaciones laborales', 3
FROM nav_items WHERE slug = 'talento';

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-contrataciones', '/contrataciones', 'Contrataciones', 4
FROM nav_items WHERE slug = 'talento';

-- Resumen visible al correr a mano — confirma que los 5 (padre + 4 hijos)
-- quedaron completos, sin importar el historial de migraciones previo.
SELECT id, parent_id, slug, ruta, label FROM nav_items
WHERE slug = 'talento' OR slug IN ('talento-hojas-de-vida', 'talento-contratos', 'talento-certificaciones-laborales', 'talento-contrataciones');
