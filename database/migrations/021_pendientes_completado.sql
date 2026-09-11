-- "Marcar como hecho" en Mis pendientes: no existía forma de aprobar/
-- tildar una tarea sin borrarla del todo (borrar la quita para siempre,
-- no deja rastro de que se hizo). Default 0 para no afectar los
-- pendientes que ya existan.

ALTER TABLE pendientes
  ADD COLUMN completado TINYINT(1) NOT NULL DEFAULT 0 AFTER titulo;
