-- 047_drive_personal_import_masivo.sql
-- Importación completa y gradual del Drive personal de cada usuario (todas
-- sus carpetas y archivos, no solo uno a la vez como ya permitía
-- DriveUsuarioController.php /gestion-documental/drive/importar). Como el
-- proyecto no tiene cola/cron, el avance real ocurre en lotes pedidos por
-- JS desde la propia pantalla de Gestión Documental (ver
-- /gestion-documental/drive/importar-todo/avanzar) — estas columnas/tablas
-- son el estado que le permite a esos lotes seguir donde quedaron.

ALTER TABLE usuarios
  ADD COLUMN google_drive_import_estado ENUM('no_iniciado','en_progreso','completo') NOT NULL DEFAULT 'no_iniciado',
  ADD COLUMN google_drive_import_fase ENUM('carpetas','archivos') NULL,
  ADD COLUMN google_drive_import_page_token VARCHAR(512) NULL,
  ADD COLUMN google_drive_import_traidos INT NOT NULL DEFAULT 0;

-- Espejo local de la jerarquía de carpetas del Drive personal de cada
-- quien — separado A PROPÓSITO de `carpetas_documentales` (esa es un árbol
-- FIJO de 2 niveles del repositorio institucional, dirección → área, sin
-- usuario_id; meterle acá una jerarquía arbitraria de una persona rompería
-- esa invariante). `drive_parent_folder_id` guarda el id crudo que Drive le
-- puso a la carpeta padre; `parent_id` es el que ya se resolvió contra esta
-- misma tabla — Drive no garantiza que una carpeta llegue paginada después
-- de su padre, así que `parent_id` se completa en una segunda pasada
-- cuando ya se trajeron todas (ver el controlador). NULL en cualquiera de
-- los dos = raíz de "Mi Drive" (o un padre que no es una carpeta propia,
-- ej. compartida por alguien más) — no es un error.
CREATE TABLE IF NOT EXISTS drive_personal_carpetas (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id              INT NOT NULL,
    drive_folder_id         VARCHAR(64) NOT NULL,
    drive_parent_folder_id  VARCHAR(64) NULL,
    parent_id               INT NULL,
    nombre                  VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_usuario_drive_folder (usuario_id, drive_folder_id),
    CONSTRAINT drive_personal_carpetas_usuario_fk FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT drive_personal_carpetas_parent_fk FOREIGN KEY (parent_id) REFERENCES drive_personal_carpetas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Archivos ya descargados del Drive personal hacia el portal — igual que
-- direccion_carpeta_archivos, pero en un storage propio
-- (storage/drive_personal/{usuario_id}/) y sin ninguna dirección de por
-- medio: es un espacio 100% privado de quien lo trajo. carpeta_id NULL =
-- el archivo vive en la raíz de "Mi Drive", no dentro de ninguna carpeta.
CREATE TABLE IF NOT EXISTS drive_personal_archivos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT NOT NULL,
    carpeta_id   INT NULL,
    drive_file_id VARCHAR(64) NOT NULL,
    nombre       VARCHAR(255) NOT NULL,
    tipo         VARCHAR(20) NULL,
    archivo      VARCHAR(255) NOT NULL,
    peso_bytes   INT NOT NULL DEFAULT 0,
    creado_en    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_drive_file (usuario_id, drive_file_id),
    CONSTRAINT drive_personal_archivos_usuario_fk FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT drive_personal_archivos_carpeta_fk FOREIGN KEY (carpeta_id) REFERENCES drive_personal_carpetas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
