-- Completa columnas que pueden faltar al restaurar un respaldo anterior a
-- las migraciones 009 y 015. Las comprobaciones hacen esta reparación
-- segura tanto para el respaldo antiguo como para una base ya actualizada.

SET @sql = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE cargos ADD COLUMN nombre VARCHAR(150) NULL AFTER cargo',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'cargos'
    AND column_name = 'nombre'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE cargos ADD COLUMN foto VARCHAR(255) NULL AFTER nombre',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'cargos'
    AND column_name = 'foto'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE cargos SET nombre = 'Martha Ruiz Delgado' WHERE codigo = '001';
UPDATE cargos SET nombre = 'Camilo Naranjo' WHERE codigo = '004';
UPDATE cargos SET nombre = 'Laura Gómez' WHERE codigo = '007';
UPDATE cargos SET nombre = 'Andrés Castaño' WHERE codigo = '028';

SET @sql = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE direccion_responsables ADD COLUMN foto VARCHAR(255) NULL AFTER cargo',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'direccion_responsables'
    AND column_name = 'foto'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
