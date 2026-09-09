-- Fase 4 — módulo Calendario. No necesita tabla nueva: reusa "eventos",
-- que ya existía y ya se mostraba como lista en Inicio ("Agenda de la
-- semana") — ver Calendario.php/PortalController.php. Solo agrega la
-- entrada de menú (ver sidebar.php, ya conectado a nav_items).

INSERT INTO nav_items (seccion_id, parent_id, slug, ruta, label, icono, orden) VALUES
  (1, NULL, 'calendario', '/calendario', 'Calendario', 'calendar3', 4);
