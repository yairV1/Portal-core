-- El Mapa del portal (/mapa-portal) mostraba cada módulo solo con ícono +
-- nombre + lista de áreas, sin decir qué es cada uno — confuso para quien
-- entra por primera vez. Se agrega una descripción corta y real por módulo
-- (ver PortalController.php, bloque '/mapa-portal', y Mapa.php).

ALTER TABLE sitemap_modulos ADD COLUMN descripcion VARCHAR(255) NULL AFTER icono;

UPDATE sitemap_modulos SET descripcion='Punto de entrada al portal: bienvenida, indicadores rápidos, accesos y pendientes del día.' WHERE label='Inicio';
UPDATE sitemap_modulos SET descripcion='Indicadores y reportes de gestión por dirección, con tableros embebidos para seguimiento directivo.' WHERE label='Tableros Estratégicos';
UPDATE sitemap_modulos SET descripcion='Planeación institucional: PDI, PEI, políticas y planes de acción de la Rectoría.' WHERE label='Gestión Institucional';
UPDATE sitemap_modulos SET descripcion='Procesos, procedimientos, auditorías y gestión de riesgos del sistema de calidad institucional.' WHERE label='Sistema de Gestión Integral';
UPDATE sitemap_modulos SET descripcion='Repositorio central de documentos institucionales, con control de versiones y archivo histórico.' WHERE label='Gestión Documental';
UPDATE sitemap_modulos SET descripcion='Organigrama, manual de funciones, evaluación de desempeño y bienestar del personal.' WHERE label='Talento Humano';
UPDATE sitemap_modulos SET descripcion='Contabilidad, tesorería, presupuesto y servicios administrativos de la institución.' WHERE label='Administrativa y Financiera';
UPDATE sitemap_modulos SET descripcion='Decanaturas, programas académicos, registro y control, y autoevaluación institucional.' WHERE label='Vicerrectoría Académica';
UPDATE sitemap_modulos SET descripcion='Grupos de investigación, semilleros, proyectos y proyección social.' WHERE label='Investigación e Innovación';
UPDATE sitemap_modulos SET descripcion='Noticias, comunicados, circulares y eventos institucionales.' WHERE label='Novedades';
UPDATE sitemap_modulos SET descripcion='Acuerdos, resoluciones y reglamentos que rigen la institución.' WHERE label='Normatividad';
UPDATE sitemap_modulos SET descripcion='Accesos directos a las herramientas y sistemas que usa la institución.' WHERE label='Aplicaciones';
