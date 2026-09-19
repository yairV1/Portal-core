-- 045_permisos_rol_acciones.sql
-- Permisos por rol A NIVEL DE ACCIÓN (crear/subir/importar/eliminar...),
-- no solo de visibilidad de módulo (eso ya lo cubre la migración
-- 044_permisos_rol_nav_item.sql / tabla permisos_rol_negados).
--
-- Motivo: la mayoría de lo que "usuario"/"admin_direccion" pueden tocar ya
-- está limitado por su direccion_id asignada (ver usuario_admin_de() en
-- public/index.php) — ocultar el módulo completo por rol es poco útil
-- cuando lo que realmente se quiere ajustar es "sí puede subir archivos
-- pero no eliminarlos", por ejemplo. Esta tabla permite eso sin tocar la
-- lógica de dirección/área, que sigue aplicando igual y primero.
--
-- Mismo modelo "solo excepciones" que permisos_rol_negados: sin fila acá,
-- la acción está permitida (comportamiento de siempre). El admin global
-- nunca pasa por esta tabla (ver usuario_puede_accion() en public/index.php).

CREATE TABLE IF NOT EXISTS permisos_rol_acciones_negadas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    rol           ENUM('admin_direccion', 'usuario') NOT NULL,
    accion_clave  VARCHAR(60) NOT NULL,
    UNIQUE KEY uq_rol_accion (rol, accion_clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
