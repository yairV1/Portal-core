-- Para poder "liberar espacio" borrando pendientes ya hechos con más de
-- un mes (ver PendienteController.php) hace falta saber CUÁNDO se
-- marcaron como hechos — no basta el booleano `completado`.

ALTER TABLE pendientes
  ADD COLUMN completado_en DATETIME NULL AFTER completado;
