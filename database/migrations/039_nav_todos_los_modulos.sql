-- Saca del sidebar los 12 ítems sueltos de Direcciones/Recursos/Analítica
-- (Gestión Institucional, SGI, Vicerrectoría Académica, Administrativa y
-- Financiera, Talento Humano, Investigación e Innovación, Gestión
-- Documental, Normatividad, Novedades, Aplicaciones, Cuadro de Mando
-- Integral —con sus 3 perspectivas hijas, se borran solas por
-- nav_items_parent_fk ON DELETE CASCADE—, Mapa del portal) y los reúne en
-- una sola página nueva ("Todos los módulos", ver ModulosController.php).
-- Ninguna ruta se borra ni cambia de permisos — cada tarjeta de la página
-- nueva apunta a la misma URL de siempre.
DELETE FROM nav_items WHERE slug IN (
  'cuadro-mando-integral', 'sitemap',
  'institucional', 'sgi', 'academica', 'financiera', 'talento', 'investigacion',
  'documental', 'normatividad', 'novedades', 'aplicaciones'
);

-- La sección "Direcciones" queda sin ítems — se borra para no dejar un
-- encabezado/divider vacío en el sidebar (sidebar.php no filtra secciones
-- vacías, las pinta igual aunque no tengan nada adentro).
DELETE FROM nav_secciones WHERE label = 'Direcciones';

INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden)
VALUES (3, NULL, 'modulos', '/modulos', 'Todos los módulos', 'grid-3x3-gap', 1);
