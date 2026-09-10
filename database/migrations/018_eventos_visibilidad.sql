-- Fase 4 — Calendario: eventos públicos/privados.
-- "eventos" hasta ahora no tenía dueño ni visibilidad: todo evento se veía
-- igual para todos. Se agrega:
--   - usuario_id: quién creó el evento (NULL en filas viejas, no hay forma
--     de saber quién las creó — se tratan igual que un evento público).
--   - visibilidad: 'publico' (todos lo ven, como hasta ahora) o 'privado'
--     (solo lo ve quien lo creó — recordatorio personal). Default 'publico'
--     para no cambiar el comportamiento de los eventos ya existentes.
-- Cualquier usuario puede crear eventos propios (antes solo admin podía
-- crear cualquier evento); marcar uno como público sigue siendo solo de
-- admin — se valida en EventoController.php, no acá.

ALTER TABLE eventos
  ADD COLUMN usuario_id INT NULL AFTER id,
  ADD COLUMN visibilidad ENUM('publico','privado') NOT NULL DEFAULT 'publico' AFTER hora_lugar,
  ADD CONSTRAINT fk_eventos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;
