-- Conecta el dashboard de Inicio a las tablas reales que ya existían
-- (ver HomeController.php) en vez de los arreglos hardcodeados en
-- inicio.js. Agrega a `kpis` lo que le faltaba para reproducir el
-- mismo look (color del tag de delta + tendencia del sparkline) y
-- limpia `accesos_rapidos.icono` para que guarde el sufijo real de
-- Bootstrap Icons en vez de un emoji.

ALTER TABLE kpis
  ADD COLUMN estado VARCHAR(20) NULL AFTER delta,
  ADD COLUMN tendencia VARCHAR(255) NULL AFTER estado;

UPDATE kpis SET estado='success', tendencia='7800,7950,8100,8300,8500,8742' WHERE label='Estudiantes activos';
UPDATE kpis SET estado='warning', tendencia='90.2,89.8,89.5,89.6,89.3,89.4' WHERE label='Retención';
UPDATE kpis SET estado='accent',  tendencia='48,52,55,58,60,62'             WHERE label='Avance PDI';
UPDATE kpis SET estado='accent',  tendencia='60,64,68,71,74,76.8'           WHERE label='Ejecución presupuestal';
UPDATE kpis SET estado='accent',  tendencia='570,575,580,585,590,594'       WHERE label='Colaboradores';

-- El nombre del ícono ("journal-bookmark") no cabía en el VARCHAR(10)
-- que traía la columna (pensado para un solo emoji) — se amplía.
ALTER TABLE accesos_rapidos MODIFY COLUMN icono VARCHAR(40) NULL;

UPDATE accesos_rapidos SET icono='journal-bookmark'  WHERE label='Manual de funciones';
UPDATE accesos_rapidos SET icono='folder2'           WHERE label='Repositorio documental';
UPDATE accesos_rapidos SET icono='folder2-open'      WHERE label='Mapa de procesos';
UPDATE accesos_rapidos SET icono='bar-chart'         WHERE label='Tablero Rectoría';
UPDATE accesos_rapidos SET icono='file-earmark-text' WHERE label='Normatividad';
UPDATE accesos_rapidos SET icono='diagram-3'         WHERE label='Organigrama';
UPDATE accesos_rapidos SET icono='tools'             WHERE label='Mesa de ayuda';
UPDATE accesos_rapidos SET icono='envelope'          WHERE label='Correo institucional';

-- Ícono de categoría por KPI (mismo lenguaje visual que ya usan los
-- accesos rápidos). "Avance PDI" no lleva — no se muestra en la franja,
-- vive en el hero.
ALTER TABLE kpis ADD COLUMN icono VARCHAR(40) NULL AFTER tendencia;
UPDATE kpis SET icono='mortarboard'  WHERE label='Estudiantes activos';
UPDATE kpis SET icono='arrow-repeat' WHERE label='Retención';
UPDATE kpis SET icono='wallet2'      WHERE label='Ejecución presupuestal';
UPDATE kpis SET icono='people'       WHERE label='Colaboradores';

-- Destino real de cada acceso rápido — antes las tarjetas no llevaban a
-- ningún lado. Interno = ruta de routes/web.php (HomeController.php le
-- antepone BASE_URL); "Correo institucional" es el único externo.
ALTER TABLE accesos_rapidos ADD COLUMN enlace VARCHAR(255) NULL AFTER icono;
UPDATE accesos_rapidos SET enlace='/talento-humano'          WHERE label='Manual de funciones';
UPDATE accesos_rapidos SET enlace='/gestion-documental'      WHERE label='Repositorio documental';
UPDATE accesos_rapidos SET enlace='/sgi'                     WHERE label='Mapa de procesos';
UPDATE accesos_rapidos SET enlace='/tableros'                WHERE label='Tablero Rectoría';
UPDATE accesos_rapidos SET enlace='/normatividad'             WHERE label='Normatividad';
UPDATE accesos_rapidos SET enlace='/talento-humano'           WHERE label='Organigrama';
UPDATE accesos_rapidos SET enlace='/aplicaciones'             WHERE label='Mesa de ayuda';
UPDATE accesos_rapidos SET enlace='https://mail.google.com'   WHERE label='Correo institucional';
