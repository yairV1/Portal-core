-- 048_talento_humano_empleados.sql
-- Tabla maestra de personal (con o sin cuenta en el portal) + 3 tablas de
-- documentos 1:N por empleado para los submódulos nuevos de Talento Humano:
-- Hojas de vida / Contratos / Certificaciones laborales (ver
-- 034_carpeta_archivos_tipo_y_talento_humano.sql, que ya dejaba comentado
-- "tipo de contrato: Término fijo/Indefinido/Prestación de servicios"
-- previendo justo esto).
--
-- `cargo` en empleados es VARCHAR libre, no FK a la tabla `cargos` (esa es
-- el organigrama estático de puestos directivos, migración 006 — forzar esa
-- FK obligaría llenar nivel/código/orden por cada empleado nuevo).
--
-- Charset/collation EXPLÍCITOS (utf8mb4/utf8mb4_unicode_ci) — mismo motivo
-- que 045_permisos_rol_modulo.sql: en Docker (MySQL 8, default
-- utf8mb4_0900_ai_ci) un JOIN futuro contra nav_items/direcciones
-- (utf8mb4_unicode_ci) fallaría con "Illegal mix of collations" sin esto.

CREATE TABLE IF NOT EXISTS empleados (
    id              INT NOT NULL AUTO_INCREMENT,
    nombre_completo VARCHAR(150) NOT NULL,
    documento       VARCHAR(30)  NOT NULL,
    cargo           VARCHAR(150) NULL,
    telefono        VARCHAR(30)  NULL,
    correo          VARCHAR(150) NULL,
    fecha_ingreso   DATE NULL,
    estado          ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    creado_en       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_empleados_documento (documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hojas_de_vida (
    id          INT NOT NULL AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    nombre      VARCHAR(150) NOT NULL,
    fecha       DATE NOT NULL,
    archivo     VARCHAR(150) NULL,
    peso_bytes  INT NULL,
    creado_en   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY empleado_id (empleado_id),
    CONSTRAINT hojas_de_vida_empleado_fk FOREIGN KEY (empleado_id) REFERENCES empleados (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contratos (
    id            INT NOT NULL AUTO_INCREMENT,
    empleado_id   INT NOT NULL,
    nombre        VARCHAR(150) NOT NULL,
    tipo_contrato ENUM('termino_fijo', 'indefinido', 'prestacion_servicios') NOT NULL,
    fecha_inicio  DATE NOT NULL,
    fecha_fin     DATE NULL,
    archivo       VARCHAR(150) NULL,
    peso_bytes    INT NULL,
    creado_en     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY empleado_id (empleado_id),
    CONSTRAINT contratos_empleado_fk FOREIGN KEY (empleado_id) REFERENCES empleados (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificaciones_laborales (
    id               INT NOT NULL AUTO_INCREMENT,
    empleado_id      INT NOT NULL,
    nombre           VARCHAR(150) NOT NULL,
    fecha_expedicion DATE NOT NULL,
    archivo          VARCHAR(150) NULL,
    peso_bytes       INT NULL,
    creado_en        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY empleado_id (empleado_id),
    CONSTRAINT certificaciones_laborales_empleado_fk FOREIGN KEY (empleado_id) REFERENCES empleados (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Submenú del sidebar bajo Talento Humano (nav_items.slug='talento', ver
-- 035_reconstruir_nav_items_faltantes.sql) — primer caso real de parent_id
-- en todo el proyecto (sidebar.php ya sabe renderizarlo, pero hoy ningún
-- item lo usa). Resuelto por SELECT del slug, no por id fijo, para no
-- depender de que Docker y el Apache nativo tengan los mismos
-- autoincrementales (ver memoria de los dos entornos en paralelo).
INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-hojas-de-vida', '/talento-humano/hojas-de-vida', 'Hojas de vida', 1
FROM nav_items WHERE slug = 'talento';

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-contratos', '/talento-humano/contratos', 'Contratos', 2
FROM nav_items WHERE slug = 'talento';

INSERT IGNORE INTO nav_items (seccion_id, parent_id, slug, ruta, label, orden)
SELECT seccion_id, id, 'talento-certificaciones-laborales', '/talento-humano/certificaciones-laborales', 'Certificaciones laborales', 3
FROM nav_items WHERE slug = 'talento';
