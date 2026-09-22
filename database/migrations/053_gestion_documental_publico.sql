-- Gestión Documental deja de mostrar "Mi Google Drive" (drive personal, ver
-- DriveUsuarioController.php — sigue existiendo pero deja de estar
-- enlazado desde esta vista) y pasa a mostrar un repositorio institucional
-- navegable con carpetas/archivos marcados como públicos.
--
-- carpetas_documentales nunca tuvo visibilidad ni soft-delete (solo se leía,
-- ver comentario en 000_esquema_base.sql) — se agregan acá. Default
-- 'privado'/inactivo=false en visibilidad para que nada quede expuesto de
-- golpe: el admin tiene que marcar explícitamente qué carpeta se ve.
--
-- archivos_documentales ya tenía `visibilidad` (migración 041, pensada para
-- lo importado del Drive personal) pero el listado de PortalController.php
-- nunca la usaba — ahora se activa para decidir qué archivo se ve acá.
-- Le falta `activo` para poder "eliminar" sin perder el registro (soft
-- delete), igual que se agrega en carpetas_documentales.
ALTER TABLE carpetas_documentales
  ADD COLUMN visibilidad ENUM('publico','privado') NOT NULL DEFAULT 'privado' AFTER label,
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER visibilidad;

ALTER TABLE archivos_documentales
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER visibilidad;
