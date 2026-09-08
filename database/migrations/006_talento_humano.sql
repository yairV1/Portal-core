-- Conecta la pestaña "Organigrama" y "Manual de funciones" de Talento
-- Humano (ver talento-humano.js) a datos reales. Los valores de los
-- INSERT son exactamente los que ya estaban quemados en el JS (ORGANIGRAMA,
-- CARGOS, COMPETENCIAS) — se migran tal cual, no se inventa contenido
-- nuevo. COMITES/KPIS_TALENTO/DESEMPENO/BIENESTAR de ese mismo archivo NO
-- se tocan acá: no encajan en ninguna de estas 4 tablas (quedan pendientes,
-- ver auditoría de datos quemados).

CREATE TABLE organigrama_niveles (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organigrama_cajas (
  id         INT NOT NULL AUTO_INCREMENT,
  nivel_id   INT NOT NULL,
  label      VARCHAR(150) NOT NULL,
  meta       VARCHAR(150) NULL,
  destacado  TINYINT(1) NOT NULL DEFAULT 0,
  orden      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY nivel_id (nivel_id),
  CONSTRAINT organigrama_cajas_ibfk_1 FOREIGN KEY (nivel_id) REFERENCES organigrama_niveles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cargos (
  id         INT NOT NULL AUTO_INCREMENT,
  cargo      VARCHAR(150) NOT NULL,
  direccion  VARCHAR(150) NOT NULL,
  nivel      VARCHAR(50)  NOT NULL,
  codigo     VARCHAR(20)  NOT NULL,
  orden      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE competencias (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  pct    TINYINT UNSIGNED NOT NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura lista, sin filas: el JS no tenía ningún contenido real de
-- manual de funciones (objetivo del cargo, funciones, requisitos...) —
-- solo el código "MF-XXX" como referencia de documento, que ya vive en
-- cargos.codigo. seccion/contenido quedan genéricos (no un set fijo de
-- columnas) para no inventar una estructura de documento sin tener el
-- contenido real que la llene.
CREATE TABLE manual_funciones (
  id         INT NOT NULL AUTO_INCREMENT,
  cargo_id   INT NOT NULL,
  seccion    VARCHAR(100) NOT NULL,
  contenido  TEXT NOT NULL,
  orden      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY cargo_id (cargo_id),
  CONSTRAINT manual_funciones_ibfk_1 FOREIGN KEY (cargo_id) REFERENCES cargos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO organigrama_niveles (id, label, orden) VALUES
  (1, 'Gobierno', 1),
  (2, 'Dirección general', 2),
  (3, 'Direcciones', 3);

INSERT INTO organigrama_cajas (nivel_id, label, meta, destacado, orden) VALUES
  (1, 'Consejo de Fundadores', 'Máxima instancia', 0, 1),
  (1, 'Consejo Directivo', 'Dirección estratégica', 0, 2),
  (1, 'Revisoría Fiscal', 'Externa', 0, 3),
  (1, 'Control Interno', 'Evaluación independiente', 0, 4),
  (2, 'Rectoría', 'Martha Ruiz Delgado', 1, 1),
  (2, 'Secretaría General', 'Actos administrativos', 0, 2),
  (2, 'Jurídica', 'Asesoría legal', 0, 3),
  (3, 'Vicerrectoría Académica', 'Camilo Naranjo', 0, 1),
  (3, 'Dirección de Planeación Estratégica y Gestión Humana', 'Laura Gómez', 0, 2),
  (3, 'Dirección Administrativa, Contable y Financiera', 'Jorge Bermúdez', 0, 3),
  (3, 'Dirección de Investigación, Proyectos e Innovación', 'Ricardo Osorio', 0, 4);

INSERT INTO cargos (cargo, direccion, nivel, codigo, orden) VALUES
  ('Rector', 'Rectoría', 'Directivo', '001', 1),
  ('Vicerrector Académico', 'Vicerrectoría Académica', 'Directivo', '004', 2),
  ('Director de Planeación Estratégica', 'Planeación y Gestión Humana', 'Directivo', '007', 3),
  ('Decano', 'Decanaturas', 'Directivo', '012', 4),
  ('Contador General', 'Administrativa y Financiera', 'Profesional', '021', 5),
  ('Líder del Sistema de Gestión Integral', 'Planeación y Gestión Humana', 'Profesional', '028', 6),
  ('Docente de tiempo completo', 'Decanaturas', 'Docente', '052', 7);

INSERT INTO competencias (label, pct, orden) VALUES
  ('Orientación al servicio', 88, 1),
  ('Trabajo en equipo', 84, 2),
  ('Pensamiento analítico', 76, 3),
  ('Competencia digital', 71, 4),
  ('Liderazgo institucional', 69, 5);
