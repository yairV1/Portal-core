-- Renombra "Tableros Estratégicos" (/tableros) a "Cuadro de Mando Integral"
-- (/cuadro-mando-integral) y lo convierte en un hub con enlaces reales a 4
-- submódulos — reusa nav_items.parent_id, que ya existía y ya lo renderiza
-- sidebar.php como submenú, pero hasta ahora ningún ítem lo usaba (ver
-- migration 005_nav_items_wireup.sql). PortalController.php lee esos mismos
-- hijos para pintar la sección de submódulos dentro de la página, así que
-- sidebar y contenido comparten una sola fuente de enlaces.

UPDATE nav_items SET slug='cuadro-mando-integral', ruta='/cuadro-mando-integral', label='Cuadro de Mando Integral' WHERE slug='tableros';

INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin)
SELECT seccion_id, id, 'cmi-finanzas', '/administrativa-financiera', 'Finanzas', 'cash-coin', 1, 0 FROM nav_items WHERE slug='cuadro-mando-integral';
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin)
SELECT seccion_id, id, 'cmi-planeacion', '/gestion-institucional', 'Planeación', 'bullseye', 2, 0 FROM nav_items WHERE slug='cuadro-mando-integral';
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin)
SELECT seccion_id, id, 'cmi-vicerrectoria-academica', '/vicerrectoria-academica', 'Vicerrectoría Académica', 'mortarboard', 3, 0 FROM nav_items WHERE slug='cuadro-mando-integral';
INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden, solo_admin)
SELECT seccion_id, id, 'cmi-investigacion', '/investigacion-innovacion', 'Dirección de Investigación', 'stars', 4, 0 FROM nav_items WHERE slug='cuadro-mando-integral';

-- El acceso rápido de Inicio hacia esta página y la tarjeta del Mapa del
-- portal apuntaban al label/ruta viejos.
UPDATE accesos_rapidos SET enlace='/cuadro-mando-integral' WHERE enlace='/tableros';
UPDATE sitemap_modulos SET label='Cuadro de Mando Integral',
  descripcion='Indicadores institucionales en tiempo real, con acceso directo a los tableros de Finanzas, Planeación, Vicerrectoría Académica e Investigación.'
  WHERE label='Tableros Estratégicos';
