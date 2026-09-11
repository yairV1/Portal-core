-- "Mis pendientes" (ver Inicio) mostraba la MISMA lista completa a
-- cualquier usuario logueado — no existía forma de saber de quién era
-- cada tarea, ni de crear una nueva desde el portal (solo se podía
-- insertar a mano en la base de datos). Se agrega el dueño real; la
-- tabla se vació en esta misma sesión así que no hace falta backfill,
-- usuario_id puede ir NOT NULL desde ya.

ALTER TABLE pendientes
  ADD COLUMN usuario_id INT NOT NULL AFTER id,
  ADD CONSTRAINT fk_pendientes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;
