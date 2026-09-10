-- Revierte migration 019: dependencia/extension/perfil_acceso quedaban
-- vacías para siempre (no hay panel de admin real que las llene y se
-- decidió no dejarlas auto-editables) y "sede" en la práctica es un dato
-- fijo real de la institución (COREDUCACIÓN opera solo en Honda, Tolima
-- — ver la landing pública), no una columna por usuario. Se deja como
-- texto fijo en portal-header.php en vez de columna.
-- "cargo" (ya existía antes de la 019) se queda en la tabla, pero deja de
-- ser editable por el propio usuario (ver PerfilController.php) para que
-- nadie se ponga a sí mismo un cargo falso tipo "Dueño".

ALTER TABLE usuarios
  DROP COLUMN dependencia,
  DROP COLUMN extension,
  DROP COLUMN perfil_acceso,
  DROP COLUMN sede;
