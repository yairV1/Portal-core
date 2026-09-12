-- Distingue "Documentación destacada" de "Formatos" (plantillas en blanco
-- para descargar y diligenciar) dentro de la misma tabla — mismo shape,
-- ambos aceptan Word/Excel/PDF, solo cambia el propósito. Ver
-- PortalController.php (separa $moduloDocumentos/$moduloFormatos por esta
-- columna) y DocumentoController.php (/documentos/crear).
ALTER TABLE direccion_documentos
    ADD COLUMN categoria ENUM('documento', 'formato') NOT NULL DEFAULT 'documento' AFTER direccion_id;
