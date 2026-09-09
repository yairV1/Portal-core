-- Formulario de contratación (ver ContratacionController.php) — acceso
-- SIEMPRE por token único, nunca listado ni adivinable (el candidato
-- maneja datos sensibles: cédula, cuenta bancaria, RUT). Un admin genera
-- el enlace desde el panel /contrataciones y se lo envía manualmente al
-- candidato (no hay integración de correo todavía).
--
-- Una sola fila por invitación: nace con token + usado=0 y datos NULL;
-- al completarse el formulario se llenan los campos y usado pasa a 1 —
-- mismo criterio que ya usa direccion_documentos.archivo (nullable hasta
-- que exista de verdad).

CREATE TABLE contrataciones (
  id                 INT NOT NULL AUTO_INCREMENT,
  token              VARCHAR(64) NOT NULL,
  postulacion_id     INT NULL,
  nombre_referencia  VARCHAR(150) NULL,
  usado              TINYINT(1) NOT NULL DEFAULT 0,

  nombre             VARCHAR(150) NULL,
  cedula             VARCHAR(30)  NULL,
  celular            VARCHAR(30)  NULL,
  email              VARCHAR(150) NULL,
  estado_civil       VARCHAR(30)  NULL,
  direccion          VARCHAR(200) NULL,
  profesion          VARCHAR(150) NULL,
  ciudad             VARCHAR(100) NULL,
  cuenta_bancaria    VARCHAR(100) NULL,
  nivel_academico    VARCHAR(100) NULL,
  eps                VARCHAR(100) NULL,
  fondo_pension      VARCHAR(100) NULL,
  arl                VARCHAR(100) NULL,
  fondo_cesantias    VARCHAR(100) NULL,

  ip                 VARCHAR(45) NULL,
  generado_en        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completado_en      TIMESTAMP NULL,

  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY postulacion_id (postulacion_id),
  CONSTRAINT contrataciones_ibfk_1 FOREIGN KEY (postulacion_id) REFERENCES postulaciones (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "tipo" es un valor (uno de los ~18 documentos del formulario), no una
-- columna — agregar un tipo de documento nuevo el día de mañana no pide
-- migración, solo un valor nuevo en el arreglo de ContratacionController.php.
CREATE TABLE contratacion_documentos (
  id                INT NOT NULL AUTO_INCREMENT,
  contratacion_id   INT NOT NULL,
  tipo              VARCHAR(60) NOT NULL,
  archivo           VARCHAR(255) NOT NULL,
  subido_en         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY contratacion_id (contratacion_id),
  CONSTRAINT contratacion_documentos_ibfk_1 FOREIGN KEY (contratacion_id) REFERENCES contrataciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Entrada de menú, admin-only (mismo criterio que Postulaciones).
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin) VALUES
  (3, NULL, 'contrataciones', '/contrataciones', 'Contrataciones', 'file-earmark-person', 7, 1);
