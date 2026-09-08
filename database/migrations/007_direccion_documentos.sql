-- Conecta "Documentación destacada" de los módulos genéricos de dirección
-- (ver gestion-institucional.js y las demás *.js del mismo patrón) a una
-- tabla real, igual que ya se hizo con direccion_kpis/direccion_areas
-- (migraciones 002/003). Se agrega al bloque genérico de PortalController.php
-- (el que ya resuelve $direccion por slug), así que sirve para las 6
-- direcciones por igual — hoy solo Gestión Institucional tiene filas.
-- Los datos son los que ya estaban quemados en gestion-institucional.js
-- (MODULO.docs), migrados tal cual; la fecha se guarda como DATE real
-- (no el texto "12 jun 2026") para formatear igual que noticias/eventos.

CREATE TABLE direccion_documentos (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  nombre        VARCHAR(200) NOT NULL,
  tipo          VARCHAR(60)  NOT NULL,
  version       VARCHAR(20)  NOT NULL,
  fecha         DATE NOT NULL,
  orden         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  CONSTRAINT direccion_documentos_ibfk_1 FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (1, 'Plan de Desarrollo Institucional 2025-2030', 'Plan',             'V2.0', '2026-06-12', 1),
  (1, 'Proyecto Educativo Institucional',            'Documento marco', 'V4.1', '2026-04-30', 2),
  (1, 'Mapa Estratégico Institucional',               'Presentación',    'V1.3', '2026-05-18', 3),
  (1, 'Política de Gobierno Corporativo',              'Política',        'V1.0', '2026-03-02', 4);
