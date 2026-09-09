-- Nombre real encontrado al migrar administrativa-financiera.js
-- (MODULO.responsables: "Sandra Cárdenas · Contadora General") — coincide
-- con el cargo "Contador General" del Directorio, que había quedado sin
-- asignar en 009_directorio.sql por no tener el nombre confirmado todavía.

UPDATE cargos SET nombre = 'Sandra Cárdenas' WHERE codigo = '021';
