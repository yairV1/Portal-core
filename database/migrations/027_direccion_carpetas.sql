-- Gestor de documentos tipo "drive" para un módulo de dirección — arranca
-- solo en Administrativa y Financiera (ver PortalController.php slug
-- 'financiera' y CarpetaController.php), pero queda genérico por
-- direccion_id para activarlo en otra dirección sin migración nueva.
--
-- Carpetas de profundidad libre (parent_id a sí misma, sin límite de
-- niveles) — a diferencia de carpetas_documentales, que usa Gestión
-- Documental, está fija a 2 niveles (dirección → área) y no tiene
-- direccion_id (es un árbol aparte, no ligado a la tabla direcciones).

CREATE TABLE direccion_carpetas (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  parent_id     INT NULL,
  nombre        VARCHAR(150) NOT NULL,
  creado_en     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  KEY parent_id (parent_id),
  CONSTRAINT direccion_carpetas_direccion_fk FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE,
  CONSTRAINT direccion_carpetas_parent_fk FOREIGN KEY (parent_id) REFERENCES direccion_carpetas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE direccion_carpeta_archivos (
  id          INT NOT NULL AUTO_INCREMENT,
  carpeta_id  INT NOT NULL,
  nombre      VARCHAR(150) NOT NULL,
  archivo     VARCHAR(255) NOT NULL,
  peso_bytes  INT UNSIGNED NOT NULL,
  subido_en   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY carpeta_id (carpeta_id),
  CONSTRAINT direccion_carpeta_archivos_fk FOREIGN KEY (carpeta_id) REFERENCES direccion_carpetas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
