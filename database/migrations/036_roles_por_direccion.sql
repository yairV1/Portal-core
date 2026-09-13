-- Habilita administradores por dirección — antes de esto 'admin' era todo
-- o nada (ve y edita TODO el portal). Ver Panel de Usuarios
-- (UsuariosController.php): un admin global puede crear usuarios con rol
-- 'admin_direccion', atados a una sola fila de `direcciones` — ese usuario
-- solo puede crear/subir/eliminar carpetas y documentos DENTRO de esa
-- dirección (ver la función usuario_admin_de() en public/index.php),
-- igual que un 'admin' normal en todo lo demás (Contenido Landing,
-- Contrataciones, Calendario, etc. siguen siendo solo para 'admin' global).

ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('admin','admin_direccion','usuario') NOT NULL DEFAULT 'usuario',
  ADD COLUMN direccion_id INT NULL AFTER rol;

ALTER TABLE usuarios
  ADD CONSTRAINT usuarios_direccion_fk FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE SET NULL;
