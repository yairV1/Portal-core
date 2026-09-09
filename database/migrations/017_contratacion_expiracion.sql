-- Expiración de enlaces de contratación (ver ContratacionController.php).
-- NOT NULL sin default: se calcula siempre al generar el enlace
-- (mínimo 15 días desde ahora, ver /contrataciones/generar) — nunca un
-- enlace queda sin fecha de vencimiento.

ALTER TABLE contrataciones ADD COLUMN expira_en DATETIME NOT NULL AFTER usado;
