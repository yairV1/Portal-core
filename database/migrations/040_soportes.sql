-- "Soportes": bitácora técnica del admin global (fallos/mejoras del propio
-- sistema) — ver SoportesController.php. Nada institucional, solo la
-- persona que administra el portal la ve y la crea (mismo criterio que
-- Panel de Usuarios: gate por rol admin, no un módulo de contenido más).
-- Mismo patrón que `pendientes` (resuelto/resuelto_en en vez de
-- completado/completado_en) — misma UX de check para marcar hecho.
CREATE TABLE IF NOT EXISTS soportes (
  id          INT NOT NULL AUTO_INCREMENT,
  titulo      VARCHAR(200) NOT NULL,
  descripcion VARCHAR(500) NULL,
  tipo        ENUM('fallo','mejora') NOT NULL DEFAULT 'fallo',
  resuelto    TINYINT(1) NOT NULL DEFAULT 0,
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resuelto_en DATETIME NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
