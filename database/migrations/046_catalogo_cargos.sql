-- 046_catalogo_cargos.sql
-- Convierte usuarios.cargo (texto libre, solo decorativo hasta ahora) en un
-- catálogo fijo para poder usarlo como llave de permisos (ver
-- permisos_cargo_negados/permisos_cargo_acciones_negadas más abajo) sin que
-- un typo ("Jefe de RRHH" vs "Jefe RRHH") cree dos grupos de permisos
-- distintos sin que nadie lo note.
--
-- OJO: esto NO es la tabla `cargos` (migración 006) — esa es el organigrama
-- institucional (Directorio/Talento Humano, datos estáticos tipo "Rector"),
-- sin ninguna relación con `usuarios`. Esta es otra cosa, por eso el nombre
-- distinto: catalogo_cargos.

CREATE TABLE IF NOT EXISTS catalogo_cargos (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_catalogo_cargos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migra los valores que ya existían en usuarios.cargo, para que nadie
-- pierda el suyo con este cambio.
INSERT INTO catalogo_cargos (nombre)
SELECT DISTINCT TRIM(cargo) FROM usuarios WHERE cargo IS NOT NULL AND TRIM(cargo) <> '';

ALTER TABLE usuarios ADD COLUMN cargo_id INT NULL AFTER cargo;

UPDATE usuarios u
JOIN catalogo_cargos c ON c.nombre = TRIM(u.cargo)
SET u.cargo_id = c.id
WHERE u.cargo IS NOT NULL AND TRIM(u.cargo) <> '';

ALTER TABLE usuarios
  ADD CONSTRAINT usuarios_cargo_fk FOREIGN KEY (cargo_id) REFERENCES catalogo_cargos (id) ON DELETE SET NULL;

ALTER TABLE usuarios DROP COLUMN cargo;

-- Mismo modelo "solo excepciones" que permisos_rol_negados/
-- permisos_rol_acciones_negadas (migraciones 044/045), pero con cargo_id en
-- vez de rol — un tercer eje de permisos que se combina con rol y
-- dirección: si CUALQUIERA de los tres niega, gana la negación (ver
-- usuario_puede_ver_ruta()/usuario_puede_accion() en public/index.php).
CREATE TABLE IF NOT EXISTS permisos_cargo_negados (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    cargo_id    INT NOT NULL,
    nav_item_id INT NOT NULL,
    UNIQUE KEY uq_cargo_navitem (cargo_id, nav_item_id),
    CONSTRAINT permisos_cargo_negados_cargo_fk FOREIGN KEY (cargo_id) REFERENCES catalogo_cargos (id) ON DELETE CASCADE,
    CONSTRAINT permisos_cargo_negados_navitem_fk FOREIGN KEY (nav_item_id) REFERENCES nav_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permisos_cargo_acciones_negadas (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    cargo_id     INT NOT NULL,
    accion_clave VARCHAR(60) NOT NULL,
    UNIQUE KEY uq_cargo_accion (cargo_id, accion_clave),
    CONSTRAINT permisos_cargo_acciones_negadas_cargo_fk FOREIGN KEY (cargo_id) REFERENCES catalogo_cargos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
