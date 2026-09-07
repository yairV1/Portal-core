-- Límite de intentos de inicio de sesión por IP (ver AuthController.php).
-- Cada fila es una IP; se borra al iniciar sesión correcto y se actualiza
-- en cada intento fallido, bloqueando esa IP un rato tras varios fallos.

CREATE TABLE IF NOT EXISTS intentos_login (
  ip              VARCHAR(45) NOT NULL PRIMARY KEY,
  intentos        INT NOT NULL DEFAULT 1,
  bloqueado_hasta DATETIME NULL,
  ultimo_intento  DATETIME NOT NULL,
  KEY idx_bloqueado_hasta (bloqueado_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
