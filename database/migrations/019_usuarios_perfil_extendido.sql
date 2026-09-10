-- El cajón de perfil (ver portal-header.php) mostraba "Rectoría", "ext.
-- 101", "Directivo · total" y "Honda, Tolima" fijos para CUALQUIER
-- usuario logueado — nunca vinieron de la base de datos, eran datos de
-- ejemplo que quedaron del diseño inicial. Se agregan las columnas reales;
-- todas NULL por defecto (no se inventa un valor de relleno) para que un
-- usuario sin este dato cargado vea "—", igual que ya pasa con "cargo".

ALTER TABLE usuarios
  ADD COLUMN dependencia VARCHAR(100) NULL AFTER cargo,
  ADD COLUMN extension VARCHAR(20) NULL AFTER dependencia,
  ADD COLUMN perfil_acceso VARCHAR(100) NULL AFTER extension,
  ADD COLUMN sede VARCHAR(100) NULL AFTER perfil_acceso;
