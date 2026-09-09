-- Permite subir/descargar el archivo real de cada documento (hoy
-- archivos_documentales solo tenía metadatos, sin PDF/Word detrás de
-- ninguna fila — ver Documental.php/DocumentoController.php). El archivo
-- se guarda fuera de public/ (ver DocumentoController.php), así que esta
-- columna solo trae el nombre físico que se le puso en disco, no una URL.
-- Nullable: las 14 filas existentes siguen sin archivo hasta que alguien
-- lo suba.

ALTER TABLE archivos_documentales
  ADD COLUMN archivo VARCHAR(255) NULL AFTER responsable;
