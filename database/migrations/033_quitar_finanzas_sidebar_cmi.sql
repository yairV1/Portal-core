-- Quita "Finanzas" del submenú de "Cuadro de Mando Integral" en el sidebar
-- (nav_items id=16 en el esquema original, ver migración 025) — la ruta
-- /cuadro-mando-integral/finanzas y su página siguen existiendo, solo deja
-- de aparecer en el menú.
DELETE FROM nav_items WHERE parent_id = 2 AND ruta = '/cuadro-mando-integral/finanzas';
