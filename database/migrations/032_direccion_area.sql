-- Separa "Administración" de "Finanzas" dentro del mismo módulo/dirección
-- (antes era un solo árbol de carpetas sin distinción) — ver
-- PortalController.php ($areaActiva) y CarpetaController.php.
-- Las subcarpetas heredan el área de su carpeta padre al crearse (no hace
-- falta guardarla calculada en cada archivo suelto: siempre se navega
-- dentro de una carpeta conocida).
ALTER TABLE direccion_carpetas
    ADD COLUMN area ENUM('administracion', 'finanzas') NOT NULL DEFAULT 'administracion' AFTER direccion_id;

ALTER TABLE direccion_documentos
    ADD COLUMN area ENUM('administracion', 'finanzas') NOT NULL DEFAULT 'administracion' AFTER categoria;
