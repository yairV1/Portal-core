-- Permite editar el perfil propio (nombre, cargo y foto) desde el panel
-- de perfil (ver PerfilController.php). La foto se guarda como ruta
-- relativa (ej. /uploads/perfiles/usuario_1.jpg); NULL = sin foto, se
-- usa el círculo con la inicial de siempre.

ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL;
