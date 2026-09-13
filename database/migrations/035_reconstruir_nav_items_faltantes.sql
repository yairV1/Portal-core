-- Reconstruye los ítems del sidebar que 000_esquema_base.sql no pudo
-- sembrar: a diferencia de directorio/calendario/postulaciones/
-- contrataciones/contenido-landing (que SÍ llegan por INSERT directo en
-- 009/010/014/016/030 y por eso ya existen), estos 13 ítems del menú
-- (Inicio, Cuadro de Mando Integral + sus 3 perspectivas, Mapa del portal,
-- las 6 direcciones, Gestión Documental, Normatividad, Novedades,
-- Aplicaciones) solo reciben UPDATE en 005/025/026/030/033 — las migraciones
-- daban por hecho que la fila ya existía desde el seed original perdido
-- (ver SEGUIMIENTO.md Problema 2/4 y el propio 000_esquema_base.sql).
--
-- Los valores de acá NO son una estimación: se copian tal cual del texto
-- de esas migraciones (slug/ruta/icono/label de cada UPDATE), aplicando ya
-- el estado FINAL después de todas las migraciones posteriores que las
-- vuelven a tocar (ej. el slug 'tableros' que 005 le pone icono nunca se
-- inserta como 'tableros' acá, porque 025 ya lo renombra a
-- 'cuadro-mando-integral' — se inserta directo con el nombre final).
--
-- orden/seccion_id de cada ítem se deduce de los pocos que SÍ tienen orden
-- explícito en una migración (005: "orden=3 WHERE slug='novedades'",
-- "orden=4 WHERE slug='aplicaciones'"; 010_calendario.sql: calendario ya
-- quedó en orden=4 de la sección Principal) — el resto se ordena en la
-- misma secuencia en que aparece en el propio comentario de 005.

-- ── Principal (seccion_id=1) — calendario ya existe en orden=4 ──
INSERT INTO nav_items (id, seccion_id, parent_id, slug, ruta, label, icono, orden) VALUES
  (6, 1, NULL, 'inicio',                '/',                     'Inicio',                     'house-door', 1),
  (7, 1, NULL, 'cuadro-mando-integral',  '/cuadro-mando-integral','Cuadro de Mando Integral',   'bar-chart',  2),
  (8, 1, NULL, 'sitemap',                '/mapa-portal',          'Mapa del portal',             'map',        3);

-- Submenú de "Cuadro de Mando Integral" (ver migración 026: 4 perspectivas
-- nuevas en `direcciones`, ids 7-10 acá). "Finanzas" se reconstruye y se
-- omite aposta: la migración 033 la borra del sidebar (la página sigue
-- existiendo, solo deja de aparecer en el menú) — insertar acá su estado
-- ya-borrado es más fiel que insertarla y tener que borrarla de nuevo.
INSERT INTO nav_items (id, seccion_id, parent_id, slug, ruta, label, orden) VALUES
  (9,  1, 7, 'cmi-planeacion',              '/cuadro-mando-integral/planeacion',              'Planeación',                 1),
  (10, 1, 7, 'cmi-vicerrectoria-academica', '/cuadro-mando-integral/vicerrectoria-academica', 'Vicerrectoría Académica',    2),
  (11, 1, 7, 'cmi-investigacion',           '/cuadro-mando-integral/investigacion',            'Dirección de Investigación', 3);

-- ── Direcciones (seccion_id=2) — mismo orden que la tabla `direcciones` ──
-- AMBIGÜEDAD: el orden exacto de "talento" dentro de este grupo no está
-- documentado en ninguna migración (su nav_item es de 005, anterior a que
-- 034 le diera fila propia en `direcciones`) — se ubica después de
-- financiera por afinidad temática (administrativa/financiera + talento
-- humano), no por evidencia directa.
INSERT INTO nav_items (id, seccion_id, parent_id, slug, ruta, label, icono, orden) VALUES
  (12, 2, NULL, 'institucional', '/gestion-institucional',    'Gestión Institucional',      'bank',         1),
  (13, 2, NULL, 'sgi',           '/sgi',                      'Sistema de Gestión Integral','folder2-open', 2),
  (14, 2, NULL, 'academica',     '/vicerrectoria-academica',  'Vicerrectoría Académica',    'mortarboard',  3),
  (15, 2, NULL, 'financiera',    '/administrativa-financiera','Administrativa y Financiera','cash-coin',    4),
  (16, 2, NULL, 'talento',       '/talento-humano',           'Talento Humano',             'people',       5),
  (17, 2, NULL, 'investigacion', '/investigacion-innovacion', 'Investigación e Innovación', 'stars',        6);

-- ── Recursos (seccion_id=3) — documental/normatividad se deducen por
-- eliminación (005 solo fija orden explícito para novedades=3/aplicaciones=4,
-- documental/normatividad quedan en 1/2 por ser los dos que ya estaban ahí
-- antes de esa migración); directorio(5)/postulaciones(6)/contrataciones(7)/
-- contenido-landing(8) ya existen.
INSERT INTO nav_items (id, seccion_id, parent_id, slug, ruta, label, icono, orden) VALUES
  (18, 3, NULL, 'documental',    '/gestion-documental', 'Gestión Documental', 'folder2',           1),
  (19, 3, NULL, 'normatividad',  '/normatividad',       'Normatividad',       'file-earmark-text', 2),
  (20, 3, NULL, 'novedades',     '/novedades',          'Novedades',          'newspaper',          3),
  (21, 3, NULL, 'aplicaciones',  '/aplicaciones',       'Aplicaciones',       'grid',               4);
