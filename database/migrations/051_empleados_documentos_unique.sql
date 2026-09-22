-- 051_empleados_documentos_unique.sql
-- Hallazgo del code-review (2026-09-21): el diseño de hojas_de_vida/
-- contratos/certificaciones_laborales asume "a lo más 1 fila por
-- empleado" (así lo asumen las vistas y los comentarios de
-- EmpleadoController.php/EmpleadoDocumentoController.php), pero la
-- migración 048 solo dejó un KEY normal en empleado_id, no UNIQUE — un
-- doble clic o un reintento de red en "Subir" podía insertar 2 filas
-- para el mismo empleado sin que la BD lo impidiera, y la vista mostraba
-- al empleado duplicado con archivos distintos.
-- Seguro de aplicar: las 3 tablas están vacías en ambos entornos
-- (verificado antes de esta migración).
ALTER TABLE hojas_de_vida DROP INDEX empleado_id, ADD UNIQUE KEY uq_hojas_de_vida_empleado (empleado_id);
ALTER TABLE contratos DROP INDEX empleado_id, ADD UNIQUE KEY uq_contratos_empleado (empleado_id);
ALTER TABLE certificaciones_laborales DROP INDEX empleado_id, ADD UNIQUE KEY uq_certificaciones_laborales_empleado (empleado_id);
