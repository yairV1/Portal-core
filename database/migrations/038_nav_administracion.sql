-- Junta los 4 ítems sueltos que eran solo_admin=1 (Usuarios, Contenido
-- landing, Contrataciones, Postulaciones — ver migraciones 014, 016, 030,
-- 037) en un solo ítem de sidebar ("Administración", ver
-- AdminController.php) que reparte hacia esas mismas 4 páginas, que siguen
-- existiendo en sus rutas de siempre — esto solo agrupa la entrada del
-- menú, ningún controlador ni ruta se mueve ni se borra.
DELETE FROM nav_items WHERE slug IN ('postulaciones', 'contrataciones', 'contenido-landing', 'usuarios');

INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, solo_admin, orden)
VALUES (3, NULL, 'administracion', '/administracion', 'Administración', 'shield-lock', 1, 6);
