-- Extiende a "Documentación destacada" de los 6 módulos genéricos de
-- dirección (Gestión Institucional, SGI, Vicerrectoría Académica,
-- Administrativa y Financiera, Investigación e Innovación, Novedades) el
-- mismo subir/descargar real que ya tiene Gestión Documental — ver
-- 008_archivos_documentales_upload.sql y DocumentoController.php
-- (generalizado con ?tipo=direccion para reusar el mismo controlador).

ALTER TABLE direccion_documentos
  ADD COLUMN archivo VARCHAR(255) NULL AFTER version;
