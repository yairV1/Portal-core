-- "Quiénes somos" (ver QuienesSomosController.php / Landing/QuienesSomos.php):
-- foto para directivos (placeholder hasta que se suba una real) y tabla
-- nueva direccion_responsables, poblada con los nombres que ya estaban
-- quemados en MODULO.responsables de los 6 JS de módulo genérico — datos
-- reales, solo movidos de lugar, no inventados.

ALTER TABLE cargos ADD COLUMN foto VARCHAR(255) NULL AFTER nombre;

CREATE TABLE direccion_responsables (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  nombre        VARCHAR(150) NOT NULL,
  cargo         VARCHAR(150) NOT NULL,
  foto          VARCHAR(255) NULL,
  orden         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  CONSTRAINT direccion_responsables_ibfk_1 FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- direccion_id=1 Gestión Institucional (gestion-institucional.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (1, 'Laura Gómez',    'Directora de Planeación Estratégica',   1),
  (1, 'Andrés Castaño', 'Líder Sistema de Gestión Integral',     2),
  (1, 'Paula Medina',   'Analista de Planeación',                3);

-- direccion_id=2 SGI (sgi.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (2, 'Andrés Castaño', 'Líder SGI',        1),
  (2, 'Mónica Ríos',    'Auditora interna', 2);

-- direccion_id=3 Vicerrectoría Académica (vicerrectoria-academica.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (3, 'Camilo Naranjo', 'Vicerrector Académico',            1),
  (3, 'Yolanda Torres', 'Registro y Control Académico',     2);

-- direccion_id=4 Administrativa y Financiera (administrativa-financiera.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (4, 'Jorge Bermúdez', 'Director Administrativo y Financiero', 1),
  (4, 'Sandra Cárdenas','Contadora General',                    2),
  (4, 'Diego Valencia', 'Tesorería y Cartera',                   3);

-- direccion_id=5 Investigación e Innovación (investigacion-innovacion.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (5, 'Ricardo Osorio', 'Director de Investigación',   1),
  (5, 'Natalia Peña',   'Coordinadora de Egresados',   2);

-- direccion_id=6 Novedades (novedades.js)
INSERT INTO direccion_responsables (direccion_id, nombre, cargo, orden) VALUES
  (6, 'Valentina Suárez', 'Comunicaciones', 1);
