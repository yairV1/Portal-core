-- Conecta el Google Drive PERSONAL de cada usuario a Gestión Documental —
-- distinto de la cuenta de servicio que ya usa CarpetaController.php para
-- "Traer de Drive" por link (ver GoogleDrive.php): acá cada quien conecta
-- SU propia cuenta (OAuth con permiso de solo lectura sobre su Drive, ver
-- DriveUsuarioController.php) y ve su propia lista de archivos, sin tener
-- que compartir nada con nadie primero.
--
-- google_drive_refresh_token: vive cifrado en Google, no acá — esto solo
-- guarda el token que Google entrega para pedir accesos nuevos sin volver
-- a mostrar la pantalla de consentimiento cada vez. NULL = no conectado.
ALTER TABLE usuarios
  ADD COLUMN google_drive_refresh_token VARCHAR(512) NULL,
  ADD COLUMN google_drive_conectado_en DATETIME NULL;

-- Mismo patrón que eventos.usuario_id/visibilidad (ver migración
-- 018_eventos_visibilidad.sql): un documento importado desde el Drive
-- personal de alguien puede quedar "privado" (solo lo ve quien lo trajo)
-- o "publico" (todos, como los documentos institucionales de siempre).
-- NULL en usuario_id = documento de siempre, no viene del Drive de nadie
-- en particular — sigue viéndose exactamente igual que hasta ahora.
ALTER TABLE archivos_documentales
  ADD COLUMN usuario_id INT NULL AFTER carpeta_id,
  ADD COLUMN visibilidad ENUM('publico','privado') NOT NULL DEFAULT 'publico' AFTER estado,
  ADD CONSTRAINT fk_archivos_documentales_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL;
