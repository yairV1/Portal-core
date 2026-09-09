-- Módulo "Trabaja con nosotros": landing pública de vacantes +
-- postulación, y panel admin para revisarlas (ver TrabajoController.php,
-- app/Views/Landing/Trabajo.php, app/Views/Portal/Postulaciones/).

CREATE TABLE vacantes (
  id             INT NOT NULL AUTO_INCREMENT,
  titulo         VARCHAR(150) NOT NULL,
  area           VARCHAR(100) NOT NULL,
  tipo_contrato  VARCHAR(60)  NOT NULL,
  descripcion    TEXT NOT NULL,
  activa         TINYINT(1) NOT NULL DEFAULT 1,
  orden          INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- vacante_id nullable: quien postula sin partir de una tarjeta específica
-- (o a una vacante que luego se cierra) igual queda con su cargo_aplicado
-- en texto libre — no depende de que la vacante siga existiendo.
CREATE TABLE postulaciones (
  id                 INT NOT NULL AUTO_INCREMENT,
  vacante_id         INT NULL,
  nombre             VARCHAR(150) NOT NULL,
  correo             VARCHAR(150) NOT NULL,
  telefono           VARCHAR(30)  NOT NULL,
  cargo_aplicado     VARCHAR(150) NOT NULL,
  hoja_vida_archivo  VARCHAR(255) NULL,
  foto_archivo       VARCHAR(255) NULL,
  ip                 VARCHAR(45)  NULL,
  creado_en          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY vacante_id (vacante_id),
  CONSTRAINT postulaciones_ibfk_1 FOREIGN KEY (vacante_id) REFERENCES vacantes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- El panel admin de postulaciones no debe aparecer en el menú de
-- cualquier usuario — todavía no existe el módulo real de áreas y
-- permisos, así que por ahora es un simple sí/no por rol, igual que ya
-- se filtra en el propio controlador.
ALTER TABLE nav_items ADD COLUMN solo_admin TINYINT(1) NOT NULL DEFAULT 0;

INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin) VALUES
  (3, NULL, 'postulaciones', '/postulaciones', 'Postulaciones', 'person-lines-fill', 6, 1);

-- Vacantes de ejemplo reales (no quemadas en el HTML — la landing las
-- trae de acá) para que la página no se vea vacía.
INSERT INTO vacantes (titulo, area, tipo_contrato, descripcion, activa, orden) VALUES
  ('Docente de Matemáticas', 'Vicerrectoría Académica', 'Tiempo completo', 'Docencia en pregrado, acompañamiento estudiantil y participación en comités curriculares.', 1, 1),
  ('Analista de Planeación Estratégica', 'Planeación y Gestión Humana', 'Tiempo completo', 'Apoyo en seguimiento al Plan de Desarrollo Institucional y al Sistema de Gestión Integral.', 1, 2),
  ('Auxiliar Administrativo y Financiero', 'Administrativa y Financiera', 'Medio tiempo', 'Soporte en procesos contables, cartera y archivo documental de la dirección.', 1, 3);
