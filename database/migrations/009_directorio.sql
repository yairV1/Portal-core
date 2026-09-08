-- Fase 4 — módulo Directorio. Agrega el nombre real de quien ocupa cada
-- cargo (ver Directorio.php/PortalController.php). Los 4 nombres vienen
-- de datos que YA existían en el proyecto — cruzados por título exacto de
-- cargo/dirección, no inventados:
--   - Rector, Vicerrector Académico, Director de Planeación Estratégica:
--     ver organigrama_cajas (migración 006_talento_humano.sql).
--   - Líder del Sistema de Gestión Integral: ver
--     gestion-institucional.js (MODULO.responsables, "Andrés Castaño ·
--     Líder Sistema de Gestión Integral").
-- Decano, Contador General y Docente de tiempo completo no tienen un
-- nombre real registrado en ningún lado del proyecto — quedan NULL
-- (mejor vacío que un nombre inventado).

ALTER TABLE cargos ADD COLUMN nombre VARCHAR(150) NULL AFTER cargo;

UPDATE cargos SET nombre = 'Martha Ruiz Delgado' WHERE codigo = '001'; -- Rector
UPDATE cargos SET nombre = 'Camilo Naranjo'       WHERE codigo = '004'; -- Vicerrector Académico
UPDATE cargos SET nombre = 'Laura Gómez'          WHERE codigo = '007'; -- Director de Planeación Estratégica
UPDATE cargos SET nombre = 'Andrés Castaño'       WHERE codigo = '028'; -- Líder del Sistema de Gestión Integral

-- Entrada de menú (ver sidebar.php, ya conectado a nav_secciones/nav_items
-- desde 005_nav_items_wireup.sql) — junto a los demás "Recursos".
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden) VALUES
  (3, NULL, 'directorio', '/directorio', 'Directorio', 'person-badge', 5);
