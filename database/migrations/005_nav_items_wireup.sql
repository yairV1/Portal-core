-- Conecta el sidebar a nav_secciones/nav_items (ver sidebar.php), que ya
-- existían pero ningún PHP las consultaba todavía. Mismo ajuste de ícono
-- que ya se hizo con accesos_rapidos/kpis/sitemap_modulos: pasa de emoji a
-- sufijo real de Bootstrap Icons (icono se amplía porque sufijos como
-- "file-earmark-text" no caben en el VARCHAR(10) pensado para un emoji).
-- Reusa los mismos sufijos que sitemap_modulos usa para el mismo label
-- (misma dirección/módulo, mismo ícono en todo el portal).

ALTER TABLE nav_items
  ADD COLUMN ruta VARCHAR(150) NULL AFTER slug,
  MODIFY COLUMN icono VARCHAR(40) NULL;

UPDATE nav_items SET ruta='/',                          icono='house-door'        WHERE slug='inicio';
UPDATE nav_items SET ruta='/tableros',                   icono='bar-chart'         WHERE slug='tableros';
UPDATE nav_items SET ruta='/mapa-portal',                icono='map'               WHERE slug='sitemap';
UPDATE nav_items SET ruta='/gestion-institucional',      icono='bank'              WHERE slug='institucional';
UPDATE nav_items SET ruta='/sgi',                        icono='folder2-open'      WHERE slug='sgi';
UPDATE nav_items SET ruta='/vicerrectoria-academica',    icono='mortarboard'       WHERE slug='academica';
UPDATE nav_items SET ruta='/administrativa-financiera',  icono='cash-coin'         WHERE slug='financiera';
UPDATE nav_items SET ruta='/talento-humano',              icono='people'            WHERE slug='talento';
UPDATE nav_items SET ruta='/investigacion-innovacion',   icono='stars'             WHERE slug='investigacion';
UPDATE nav_items SET ruta='/novedades',                  icono='newspaper'         WHERE slug='novedades';
UPDATE nav_items SET ruta='/gestion-documental',         icono='folder2'           WHERE slug='documental';
UPDATE nav_items SET ruta='/normatividad',                icono='file-earmark-text' WHERE slug='normatividad';
UPDATE nav_items SET ruta='/aplicaciones',                icono='grid'              WHERE slug='aplicaciones';

-- "Novedades" estaba mal ubicado en la sección "Direcciones" (seccion_id=2);
-- en el sidebar real siempre vivió en "Recursos" junto con Gestión
-- Documental/Normatividad/Aplicaciones. Se corrige sección y orden.
UPDATE nav_items SET seccion_id=3, orden=3 WHERE slug='novedades';
UPDATE nav_items SET orden=4 WHERE slug='aplicaciones';

-- Qué secciones muestran su título ("Recursos" sí lo mostraba, "Principal"
-- y "Direcciones" nunca tuvieron encabezado visible) — dato, no un if
-- quemado en sidebar.php por id de sección.
ALTER TABLE nav_secciones ADD COLUMN mostrar_titulo TINYINT(1) NOT NULL DEFAULT 1;
UPDATE nav_secciones SET mostrar_titulo=0 WHERE label IN ('Principal','Direcciones');
