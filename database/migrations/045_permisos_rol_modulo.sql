-- "Permisos por rol" deja de colgar de nav_items: la migración 039 borró los
-- nav_items de casi todos los módulos, así que el panel solo podía vetar 4.
-- Ahora la lista de módulos vive en código (config/modulos.php) y los vetos
-- se guardan por CLAVE de módulo ('sgi', 'calendario', …) en una tabla nueva.
--
-- Modelo "solo excepciones", igual que antes: una fila = acceso DENEGADO a ese
-- módulo para ese rol; sin fila = permitido.
--
-- NO borra permisos_rol_negados (044): queda intacta, sin uso, por si hay que
-- volver atrás o auditar. Idempotente: se puede correr N veces (CREATE ... IF
-- NOT EXISTS, guarda con information_schema y INSERT IGNORE).
--
-- Filas viejas SIN módulo equivalente (p. ej. '/administracion', que es solo
-- de admin y ya no se puede vetar) NO se descartan en silencio: quedan
-- listadas en permisos_rol_sin_modulo y en el SELECT del final.

CREATE TABLE IF NOT EXISTS permisos_rol_modulo (
  modulo VARCHAR(60) NOT NULL,
  rol ENUM('admin_direccion', 'usuario') NOT NULL,
  PRIMARY KEY (modulo, rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permisos_rol_sin_modulo (
  nav_item_id INT NOT NULL,
  ruta VARCHAR(255) NULL,
  rol ENUM('admin_direccion', 'usuario') NOT NULL,
  PRIMARY KEY (nav_item_id, rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- nav_items.ruta (la de cada módulo cuando era un ítem del sidebar) → clave
-- de config/modulos.php. Solo sirve para esta copia; el resto del sistema ya
-- no lee nav_items para permisos.
DROP TEMPORARY TABLE IF EXISTS tmp_mapa_modulos;
CREATE TEMPORARY TABLE tmp_mapa_modulos (
  ruta VARCHAR(255) NOT NULL PRIMARY KEY,
  modulo VARCHAR(60) NOT NULL
);
INSERT INTO tmp_mapa_modulos (ruta, modulo) VALUES
  ('/gestion-institucional', 'institucional'),
  ('/sgi', 'sgi'),
  ('/vicerrectoria-academica', 'academica'),
  ('/administrativa-financiera', 'financiera'),
  ('/talento-humano', 'talento-humano'),
  ('/investigacion-innovacion', 'investigacion'),
  ('/gestion-documental', 'gestion-documental'),
  ('/normatividad', 'normatividad'),
  ('/novedades', 'novedades'),
  ('/aplicaciones', 'aplicaciones'),
  ('/directorio', 'directorio'),
  ('/calendario', 'calendario'),
  ('/trello', 'trello'),
  ('/cuadro-mando-integral', 'cuadro-mando-integral'),
  ('/mapa-portal', 'mapa-portal'),
  ('/modulos', 'modulos');

-- Copia de vetos: solo si la tabla vieja existe (guarda como la de la 028).
SET @tabla_vieja = (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'permisos_rol_negados'
);

SET @sql = IF(@tabla_vieja = 0, 'SELECT 1',
  'INSERT IGNORE INTO permisos_rol_modulo (modulo, rol)
   SELECT m.modulo, n.rol
   FROM permisos_rol_negados n
   JOIN nav_items ni ON ni.id = n.nav_item_id
   JOIN tmp_mapa_modulos m ON m.ruta = ni.ruta'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(@tabla_vieja = 0, 'SELECT 1',
  'INSERT IGNORE INTO permisos_rol_sin_modulo (nav_item_id, ruta, rol)
   SELECT n.nav_item_id, ni.ruta, n.rol
   FROM permisos_rol_negados n
   JOIN nav_items ni ON ni.id = n.nav_item_id
   LEFT JOIN tmp_mapa_modulos m ON m.ruta = ni.ruta
   WHERE m.ruta IS NULL'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TEMPORARY TABLE IF EXISTS tmp_mapa_modulos;

-- Resumen visible al correr la migración a mano (mysql < 045_…sql): qué se
-- copió y, sobre todo, qué NO tuvo equivalente y hay que revisar.
SELECT 'vetos copiados a permisos_rol_modulo' AS resumen, COUNT(*) AS filas FROM permisos_rol_modulo;
SELECT 'SIN módulo equivalente (revisar a mano)' AS aviso, nav_item_id, ruta, rol FROM permisos_rol_sin_modulo;
