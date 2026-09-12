-- Ítem de sidebar para el nuevo panel de administración de la landing
-- pública (ver ContenidoLandingController.php) — solo_admin=1 porque edita
-- contenido institucional visible para todo público sin sesión.
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, solo_admin, orden)
VALUES (3, NULL, 'contenido-landing', '/contenido-landing', 'Contenido landing', 'window-stack', 1, 8);
