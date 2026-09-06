-- Conecta Mapa del portal, Tableros, Gestión Documental y las 6 páginas
-- de "módulo genérico" a las tablas reales que ya existían (ver
-- PortalController.php). Limpia sitemap_modulos.icono para que guarde
-- el sufijo real de Bootstrap Icons en vez de un emoji — mismo ajuste
-- que ya se hizo antes con accesos_rapidos/kpis.

ALTER TABLE sitemap_modulos MODIFY COLUMN icono VARCHAR(40) NULL;
UPDATE sitemap_modulos SET icono='house-door'        WHERE label='Inicio';
UPDATE sitemap_modulos SET icono='bar-chart'         WHERE label='Tableros Estratégicos';
UPDATE sitemap_modulos SET icono='bank'              WHERE label='Gestión Institucional';
UPDATE sitemap_modulos SET icono='folder2-open'      WHERE label='Sistema de Gestión Integral';
UPDATE sitemap_modulos SET icono='folder2'           WHERE label='Gestión Documental';
UPDATE sitemap_modulos SET icono='people'            WHERE label='Talento Humano';
UPDATE sitemap_modulos SET icono='cash-coin'         WHERE label='Administrativa y Financiera';
UPDATE sitemap_modulos SET icono='mortarboard'       WHERE label='Vicerrectoría Académica';
UPDATE sitemap_modulos SET icono='stars'             WHERE label='Investigación e Innovación';
UPDATE sitemap_modulos SET icono='newspaper'         WHERE label='Novedades';
UPDATE sitemap_modulos SET icono='file-earmark-text' WHERE label='Normatividad';
UPDATE sitemap_modulos SET icono='grid'              WHERE label='Aplicaciones';
