-- Migra "Documentación destacada" de los 5 módulos genéricos que faltaban
-- (Gestión Institucional ya se hizo en 007_direccion_documentos.sql) —
-- valores tal cual estaban quemados en cada *.js, solo convertidos a
-- fecha real. Después de esto, MODULO.docs se puede borrar de cada JS
-- porque Financiera.php/etc. van a renderizarlo en PHP con estos datos
-- (mismo patrón que ya tiene Gestion_Ins.php).

-- Sistema de Gestión Integral (direccion_id=2)
INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (2, 'Mapa de Procesos Institucional',        'Caracterización', 'V3.0', '2026-07-21', 1),
  (2, 'Procedimiento de Control Documental',   'Procedimiento',   'V2.2', '2026-07-10', 2),
  (2, 'Matriz de Riesgos Institucional',        'Formato',         'V4.0', '2026-07-05', 3),
  (2, 'Programa Anual de Auditorías',           'Plan',            'V1.0', '2026-02-15', 4);

-- Vicerrectoría Académica (direccion_id=3)
INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (3, 'Reglamento Estudiantil',                 'Reglamento', 'V5.0', '2026-07-20', 1),
  (3, 'Calendario Académico 2026-II',            'Cronograma', 'V1.1', '2026-07-01', 2),
  (3, 'Estatuto Docente',                        'Reglamento', 'V3.0', '2026-04-14', 3),
  (3, 'Plan de Autoevaluación Institucional',    'Plan',       'V2.0', '2026-05-28', 4);

-- Administrativa y Financiera (direccion_id=4)
INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (4, 'Estados Financieros 2025 (auditados)',      'Informe',       'V1.0', '2026-03-31', 1),
  (4, 'Presupuesto Institucional 2026',            'Plan',          'V2.0', '2026-01-15', 2),
  (4, 'Procedimiento de Compras y Contratación',   'Procedimiento', 'V3.1', '2026-06-22', 3),
  (4, 'Política de Cartera y Cobranza',            'Política',      'V1.2', '2026-05-09', 4);

-- Investigación e Innovación (direccion_id=5)
INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (5, 'Política de Investigación',                'Política', 'V2.0', '2026-06-17', 1),
  (5, 'Formato de Presentación de Proyectos',      'Formato',  'V1.4', '2026-06-03', 2),
  (5, 'Informe de Proyección Social 2025',         'Informe',  'V1.0', '2026-02-26', 3);

-- Novedades (direccion_id=6)
INSERT INTO direccion_documentos (direccion_id, nombre, tipo, version, fecha, orden) VALUES
  (6, 'Circular 061 · Cierre académico 2026-II', 'Circular',  'V1.0', '2026-07-29', 1),
  (6, 'Comunicado Rectoría · Acreditación',       'Comunicado', 'V1.0', '2026-07-24', 2),
  (6, 'Boletín Institucional Julio',              'Boletín',    'V1.0', '2026-07-18', 3);
