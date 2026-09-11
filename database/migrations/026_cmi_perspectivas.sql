-- Los 4 enlaces de "Cuadro de Mando Integral" (Finanzas/Planeación/
-- Vicerrectoría Académica/Dirección de Investigación) dejan de apuntar a
-- las páginas de dirección que ya existían y pasan a rutas propias, nuevas
-- y todavía vacías (a pedido del usuario: que no se toquen/afecten las
-- páginas de dirección reales, esto es un espacio aparte). Reusan el mismo
-- "módulo genérico de dirección" (ver PortalController.php, bloque
-- `!empty($modulo['slug'])`) con 4 filas nuevas en `direcciones` — sin
-- ninguna fila en direccion_kpis/direccion_areas/direccion_documentos
-- todavía, así que la página sale honestamente vacía ("Sin indicadores/
-- áreas/documentos por ahora") en vez de inventar contenido.

INSERT INTO direcciones (slug, kicker, titulo, descripcion) VALUES
('cmi-finanzas', 'Cuadro de Mando Integral', 'Finanzas', 'Perspectiva financiera del Cuadro de Mando Integral: ejecución presupuestal, ingresos y sostenibilidad económica institucional. Contenido en construcción.'),
('cmi-planeacion', 'Cuadro de Mando Integral', 'Planeación', 'Perspectiva de planeación del Cuadro de Mando Integral: avance del PDI/PEI y cumplimiento de metas institucionales. Contenido en construcción.'),
('cmi-vicerrectoria-academica', 'Cuadro de Mando Integral', 'Vicerrectoría Académica', 'Perspectiva académica del Cuadro de Mando Integral: matrícula, permanencia y calidad de los programas. Contenido en construcción.'),
('cmi-investigacion', 'Cuadro de Mando Integral', 'Dirección de Investigación', 'Perspectiva de investigación del Cuadro de Mando Integral: proyectos, semilleros y producción científica. Contenido en construcción.');

UPDATE nav_items SET ruta='/cuadro-mando-integral/finanzas' WHERE slug='cmi-finanzas';
UPDATE nav_items SET ruta='/cuadro-mando-integral/planeacion' WHERE slug='cmi-planeacion';
UPDATE nav_items SET ruta='/cuadro-mando-integral/vicerrectoria-academica' WHERE slug='cmi-vicerrectoria-academica';
UPDATE nav_items SET ruta='/cuadro-mando-integral/investigacion' WHERE slug='cmi-investigacion';
