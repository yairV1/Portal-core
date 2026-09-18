-- Sincronización automática hacia el Google Drive PERSONAL de quien sube/
-- crea/edita un documento (ver GoogleDrive.php función
-- google_drive_oauth_sincronizar_archivo() y su uso en CarpetaController.php/
-- DocumentoController.php/EditorController.php) — decisión explícita del
-- cliente tras confirmar que aplica a TODO, sin excepción, incluyendo
-- documentos con datos de terceros (Talento Humano, etc.).
--
-- google_drive_file_id: el id que Google le puso al archivo la primera vez
-- que se subió — se guarda para que la SIGUIENTE vez (otra edición, otra
-- subida sobre la misma fila) actualice ESE mismo archivo en Drive en vez
-- de crear uno nuevo cada vez. NULL = todavía no se ha sincronizado (nunca
-- se subió, o quien lo subió no tenía su Drive conectado en ese momento).
ALTER TABLE direccion_carpeta_archivos
  ADD COLUMN google_drive_file_id VARCHAR(64) NULL;

ALTER TABLE direccion_documentos
  ADD COLUMN google_drive_file_id VARCHAR(64) NULL;

ALTER TABLE archivos_documentales
  ADD COLUMN google_drive_file_id VARCHAR(64) NULL;
