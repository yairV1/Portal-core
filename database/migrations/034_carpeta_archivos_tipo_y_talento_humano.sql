-- "tipo" en cada archivo de carpeta (ej: tipo de contrato — Término fijo,
-- Indefinido, Prestación de servicios) — opcional, se llena solo cuando
-- tiene sentido (Contratos), el resto de categorías lo dejan vacío.
ALTER TABLE direccion_carpeta_archivos
    ADD COLUMN tipo VARCHAR(100) NULL AFTER nombre;

-- Dirección real de Talento Humano para el centro documental (Hojas de
-- vida / Contratos / Certificaciones laborales) — mismo mecanismo que ya
-- usa Administrativa y Financiera, ver ContenidoLandingController y
-- CarpetaController.php.
INSERT INTO direcciones (slug, titulo)
SELECT 'talento-humano', 'Talento Humano'
WHERE NOT EXISTS (SELECT 1 FROM direcciones WHERE slug = 'talento-humano');
