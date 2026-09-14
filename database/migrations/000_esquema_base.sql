-- ══════════════════════════════════════════════════════════════════════
-- 000_esquema_base.sql
--
-- Esquema base reconstruido. Este archivo reemplaza al histórico
-- "000_usuarios.sql" (ver SEGUIMIENTO.md, Problema 2 y Problema 4) que se
-- corrigió en la máquina de un compañero pero nunca se commiteó al repo.
-- Sin este archivo, una base de datos nueva no tiene ni siquiera la tabla
-- `usuarios`, y las migraciones 001-040 (que solo hacen ALTER TABLE/INSERT/
-- UPDATE sobre tablas que dan por hechas) fallan desde la primera.
--
-- RECONSTRUCCIÓN: se leyeron completas las 34 migraciones existentes
-- (database/migrations/001_*.sql .. 040_*.sql) y los 12 controladores de
-- app/Controllers/*.php (más app/Views/layouts/sidebar.php, la única vista
-- que consulta $pdo directamente) para inferir, de cada tabla que NUNCA
-- aparece en un CREATE TABLE dentro de 001-034, sus columnas base: las que
-- ya debían existir ANTES de que la primera migración le hiciera un ALTER,
-- según el propio texto de cada ALTER ("ADD COLUMN" = no estaba,
-- "MODIFY COLUMN" = sí estaba con otro tamaño/tipo) y según qué columnas
-- lee/escribe cada SELECT/INSERT/UPDATE de los controladores.
--
-- Tablas que SÍ ya tienen su CREATE TABLE en 001-034 y por lo tanto NO se
-- tocan acá: intentos_login (001), organigrama_niveles/organigrama_cajas/
-- cargos/competencias/manual_funciones (006), direccion_documentos (007),
-- vacantes/postulaciones (014), direccion_responsables (015),
-- contrataciones/contratacion_documentos (016), direccion_carpetas/
-- direccion_carpeta_archivos (027), landing_secciones/landing_estadisticas/
-- landing_modulos/landing_roles/landing_pasos (029).
--
-- Tablas verificadas como FALSOS POSITIVOS de un grep anterior (no son
-- tablas reales, no se tocan): ninguna de las de la lista de verificación
-- resultó serlo — `cumpleanos`, `ejecucion_presupuestal`, `matricula_facultad`,
-- `alertas_indicador`, `kpis_tablero` y `documentos` SÍ son tablas reales,
-- todas consultadas con SELECT/INSERT directo desde HomeController.php o
-- PortalController.php (ver el bloque '/cuadro-mando-integral' de este
-- último para las 4 primeras, y HomeController.php para `documentos`/
-- `cumpleanos`). No se encontraron alias SQL disfrazados de tabla.
--
-- SEEDS: este archivo NO reconstruye datos de contenido/cosméticos (kpis,
-- accesos_rapidos, sitemap_modulos, noticias, documentos, cumpleanos,
-- nav_items, etc.) — esas filas nunca se conocieron con certeza (no hay
-- INSERT de ellas en ninguna migración 001-034, solo UPDATE/ALTER sobre
-- filas que se asumían ya existentes) y el propio equipo, según los
-- comentarios de las migraciones, evita inventar contenido institucional.
-- Sobre una base nueva, esos UPDATE simplemente no afectan ninguna fila
-- (no son un error SQL, solo dejan el portal con esas secciones vacías).
-- SÍ se incluye un seed MÍNIMO, imprescindible para que no truene por
-- violación de FOREIGN KEY, en las dos tablas donde migraciones
-- posteriores hacen INSERT con un id de padre escrito a mano:
--   - nav_secciones: migraciones 009/010/014/016/030 insertan en
--     nav_items con seccion_id=1 o seccion_id=3 a mano.
--   - direcciones: migración 007 inserta en direccion_documentos con
--     direccion_id=1, y migración 012 con direccion_id=2..6.
-- Ver el comentario de cada tabla para el detalle y la evidencia.
-- ══════════════════════════════════════════════════════════════════════


-- ── usuarios ──────────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 004 agrega `foto`; 019 agrega y 023
-- vuelve a quitar dependencia/extension/perfil_acceso/sede; 018 le agrega
-- una FK desde `eventos.usuario_id`; 020 una FK desde `pendientes.usuario_id`).
-- Columnas base (antes de cualquier ALTER) inferidas de:
--   - AuthController.php: `SELECT id, nombre, correo, password_hash, cargo,
--     rol, foto FROM usuarios` (login) y lo mismo sin password_hash (login
--     con Google) — foto se excluye del set base porque la agrega 004.
--   - PerfilController.php: UPDATE ... SET nombre = :nombre [, foto = :foto].
--   - SEGUIMIENTO.md (Problema 2, "corregido localmente, sin commitear"):
--     agregar `creado_en`, `rol` como ENUM('admin','usuario'), tamaños de
--     VARCHAR ajustados — se aplica acá tal cual quedó documentado.
--   - password_hash: se guarda con password_hash() de PHP (bcrypt, ~60
--     caracteres) — VARCHAR(255) es el tamaño convencional para eso.
-- AMBIGÜEDAD PARA REVISAR: los tamaños exactos de VARCHAR (nombre/correo/
-- cargo) son una estimación razonable, no un valor confirmado en código —
-- SEGUIMIENTO.md solo dice "se ajustaron... según el esquema real en
-- producción" sin dar los números. `correo` se declara UNIQUE porque el
-- login busca por ese campo asumiendo una sola cuenta por correo (no hay
-- evidencia directa de la restricción en código, es una inferencia de
-- diseño razonable).
CREATE TABLE IF NOT EXISTS usuarios (
  id             INT NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(150) NOT NULL,
  correo         VARCHAR(150) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  cargo          VARCHAR(150) NULL,
  rol            ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  creado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── nav_secciones ─────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 005 agrega `mostrar_titulo`).
-- Columnas base inferidas de sidebar.php: `SELECT id, label, mostrar_titulo
-- FROM nav_secciones ORDER BY orden` (mostrar_titulo se excluye del set
-- base, la agrega 005) y del propio 005: `UPDATE nav_secciones SET
-- mostrar_titulo=0 WHERE label IN ('Principal','Direcciones')`.
--
-- SEED MÍNIMO (imprescindible, no cosmético): varias migraciones insertan
-- en nav_items con un `seccion_id` escrito a mano que debe existir para no
-- violar la FOREIGN KEY de nav_items.seccion_id (ver más abajo):
--   - 010_calendario.sql:            seccion_id = 1
--   - 009/014/016/030 (directorio,
--     postulaciones, contrataciones,
--     contenido-landing):            seccion_id = 3
-- El id=1 ("Principal") y el id=3 ("Recursos") se deducen de 005: la
-- sección "Recursos" es la única de las tres que SÍ mostraba título antes
-- de esa migración (`mostrar_titulo=0 WHERE label IN ('Principal',
-- 'Direcciones')` implica que "Recursos" es la tercera, no listada ahí).
-- El id=2 ("Direcciones") se confirma en el comentario de 005: "estaba mal
-- ubicado en la sección 'Direcciones' (seccion_id=2)".
CREATE TABLE IF NOT EXISTS nav_secciones (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO nav_secciones (id, label, orden) VALUES
  (1, 'Principal', 1),
  (2, 'Direcciones', 2),
  (3, 'Recursos', 3);


-- ── nav_items ─────────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 005 agrega `ruta` y amplía `icono`
-- de VARCHAR(10) a VARCHAR(40); 014 agrega `solo_admin`).
-- Columnas base inferidas de sidebar.php: `SELECT id, seccion_id, parent_id,
-- slug, ruta, label, icono, solo_admin FROM nav_items` (ruta y solo_admin
-- se excluyen del set base) y del propio 005: "icono se amplía porque
-- sufijos como 'file-earmark-text' no caben en el VARCHAR(10) pensado para
-- un emoji" — confirma que la columna ya existía como VARCHAR(10).
-- parent_id (self-FK) ya se documenta como existente en 005: "nav_items...
-- con parent_id para anidar" se usa recién en 025, pero la columna ya
-- estaba desde antes según sidebar.php.
-- No se agrega seed acá (a diferencia de nav_secciones/direcciones, ningún
-- INSERT de 001-034 depende de que ya existan filas en nav_items) — las
-- UPDATE de 005 sobre slugs como 'inicio'/'tableros'/etc. simplemente no
-- afectan ninguna fila en una base nueva, sin que eso sea un error SQL.
CREATE TABLE IF NOT EXISTS nav_items (
  id          INT NOT NULL AUTO_INCREMENT,
  seccion_id  INT NOT NULL,
  parent_id   INT NULL,
  slug        VARCHAR(60) NOT NULL,
  label       VARCHAR(100) NOT NULL,
  icono       VARCHAR(10) NULL,
  orden       INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug),
  KEY seccion_id (seccion_id),
  KEY parent_id (parent_id),
  CONSTRAINT nav_items_seccion_fk FOREIGN KEY (seccion_id) REFERENCES nav_secciones (id) ON DELETE CASCADE,
  CONSTRAINT nav_items_parent_fk FOREIGN KEY (parent_id) REFERENCES nav_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── direcciones ───────────────────────────────────────────────────────
-- Nunca creada en 001-034 (nunca recibe ALTER tampoco — solo se le hace
-- INSERT/SELECT). Columnas inferidas de PortalController.php: `SELECT id,
-- kicker, titulo, descripcion FROM direcciones WHERE slug = :slug` y de
-- los INSERT de las migraciones 026 (slug, kicker, titulo, descripcion) y
-- 034 (slug, titulo — kicker/descripcion se omiten, confirmando que son
-- nullable). CarpetaController.php confirma `SELECT id, slug FROM direcciones`.
--
-- SEED MÍNIMO (imprescindible, no cosmético): la migración 007 inserta en
-- direccion_documentos con direccion_id=1, y la 012 con direccion_id=2..6
-- — ambas violarían la FOREIGN KEY direccion_documentos.direccion_id si
-- esas 6 filas no existen ya. El orden/id de cada una se confirma con el
-- comentario de 015_quienes_somos.sql, que enumera "direccion_id=1 Gestión
-- Institucional... direccion_id=6 Novedades" en ese orden exacto — el
-- mismo orden en que aparecen los 6 módulos genéricos en PortalController.php
-- ($modulos, por slug). "Talento Humano" NO es una de las 6 originales:
-- la migración 034 la agrega después como una fila nueva (slug
-- 'talento-humano'), a propósito no se preinserta acá.
-- AMBIGÜEDAD PARA REVISAR: `kicker`/`descripcion` de estas 6 filas
-- originales no están documentados en ningún lado del código — se dejan
-- NULL en vez de inventar un texto institucional.
CREATE TABLE IF NOT EXISTS direcciones (
  id           INT NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(60) NOT NULL,
  kicker       VARCHAR(100) NULL,
  titulo       VARCHAR(150) NOT NULL,
  descripcion  TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO direcciones (id, slug, titulo) VALUES
  (1, 'institucional', 'Gestión Institucional'),
  (2, 'sgi', 'Sistema de Gestión Integral'),
  (3, 'academica', 'Vicerrectoría Académica'),
  (4, 'financiera', 'Administrativa y Financiera'),
  (5, 'investigacion', 'Investigación e Innovación'),
  (6, 'novedades', 'Novedades');


-- ── direccion_kpis / direccion_areas / direccion_software ───────────────
-- Ninguna de las tres se crea ni se toca con ALTER/INSERT en 001-034 —
-- solo se leen. Columnas inferidas de PortalController.php:
--   direccion_kpis:     SELECT label, valor FROM direccion_kpis WHERE direccion_id = :id ORDER BY orden
--   direccion_areas:    SELECT label, meta  FROM direccion_areas WHERE direccion_id = :id ORDER BY orden
--                        (también sidebar.php: SELECT d.slug, a.label FROM direccion_areas a JOIN direcciones d ...)
--   direccion_software: SELECT nombre       FROM direccion_software WHERE direccion_id = :id ORDER BY orden
-- Sin filas de por sí no rompe nada (los SELECT devuelven vacío), así que
-- no llevan seed.
CREATE TABLE IF NOT EXISTS direccion_kpis (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  label         VARCHAR(100) NOT NULL,
  valor         VARCHAR(50) NOT NULL,
  orden         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  CONSTRAINT direccion_kpis_fk FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS direccion_areas (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  label         VARCHAR(150) NOT NULL,
  meta          VARCHAR(150) NULL,
  orden         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  CONSTRAINT direccion_areas_fk FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS direccion_software (
  id            INT NOT NULL AUTO_INCREMENT,
  direccion_id  INT NOT NULL,
  nombre        VARCHAR(150) NOT NULL,
  orden         INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY direccion_id (direccion_id),
  CONSTRAINT direccion_software_fk FOREIGN KEY (direccion_id) REFERENCES direcciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── kpis ──────────────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 002 agrega estado/tendencia/icono).
-- Columnas base inferidas de HomeController.php: `SELECT valor, delta FROM
-- kpis WHERE label = 'Avance PDI'` y `SELECT label, valor, delta, estado,
-- tendencia, icono FROM kpis WHERE label <> 'Avance PDI' ORDER BY orden`
-- (estado/tendencia/icono se excluyen del set base, los agrega 002).
-- AMBIGÜEDAD: `valor` es texto libre (ej. "87.4%"), no numérico — se
-- parsea con regex en PHP; `delta` idem (texto tipo "+2.3% vs mes
-- anterior"). Tamaños de VARCHAR son estimación razonable.
CREATE TABLE IF NOT EXISTS kpis (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  valor  VARCHAR(50) NOT NULL,
  delta  VARCHAR(50) NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── accesos_rapidos ───────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 002 amplía `icono` de VARCHAR(10) a
-- VARCHAR(40) y agrega `enlace`). Columnas base inferidas de
-- HomeController.php: `SELECT label, meta, icono, enlace FROM
-- accesos_rapidos ORDER BY orden` (enlace se excluye, lo agrega 002) y del
-- propio 002: "el nombre del ícono... no cabía en el VARCHAR(10) que traía
-- la columna (pensado para un solo emoji)" — confirma icono VARCHAR(10) base.
CREATE TABLE IF NOT EXISTS accesos_rapidos (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  meta   VARCHAR(150) NULL,
  icono  VARCHAR(10) NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── sitemap_modulos / sitemap_items ──────────────────────────────────
-- Ninguna de las dos se crea en 001-034. sitemap_modulos solo recibe
-- ALTER (003 amplía `icono` de VARCHAR(10) a VARCHAR(40); 024 agrega
-- `descripcion`); sitemap_items nunca se toca, solo se lee.
-- Columnas base de sitemap_modulos inferidas de PortalController.php:
-- `SELECT id, nivel, label, icono, descripcion FROM sitemap_modulos ORDER
-- BY orden` (descripcion se excluye, la agrega 024) y del propio 003:
-- "Limpia sitemap_modulos.icono... mismo ajuste que ya se hizo antes con
-- accesos_rapidos/kpis" (ícono ya existía, tamaño pequeño tipo emoji).
-- Columnas de sitemap_items de PortalController.php: `SELECT label FROM
-- sitemap_items WHERE modulo_id = :id ORDER BY orden`.
-- AMBIGÜEDAD PARA REVISAR: no hay evidencia directa de qué valores toma
-- `nivel` (ni un ENUM documentado, ni un INSERT/UPDATE que lo use en
-- ninguna migración) — se deja como VARCHAR genérico.
CREATE TABLE IF NOT EXISTS sitemap_modulos (
  id     INT NOT NULL AUTO_INCREMENT,
  nivel  VARCHAR(50) NOT NULL,
  label  VARCHAR(100) NOT NULL,
  icono  VARCHAR(10) NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sitemap_items (
  id         INT NOT NULL AUTO_INCREMENT,
  modulo_id  INT NOT NULL,
  label      VARCHAR(150) NOT NULL,
  orden      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY modulo_id (modulo_id),
  CONSTRAINT sitemap_items_modulo_fk FOREIGN KEY (modulo_id) REFERENCES sitemap_modulos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── eventos ───────────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 018 agrega usuario_id + FK a
-- usuarios, y visibilidad). Columnas base inferidas de HomeController.php:
-- `SELECT titulo, fecha, hora_lugar FROM eventos WHERE visibilidad =
-- 'publico' OR usuario_id = :usuario_id` (usuario_id/visibilidad se
-- excluyen, los agrega 018) y de EventoController.php (INSERT/UPDATE con
-- titulo/fecha/hora_lugar). El propio 018 confirma: "'eventos' hasta ahora
-- no tenía dueño ni visibilidad: todo evento se veía igual para todos".
CREATE TABLE IF NOT EXISTS eventos (
  id          INT NOT NULL AUTO_INCREMENT,
  titulo      VARCHAR(150) NOT NULL,
  fecha       DATE NOT NULL,
  hora_lugar  VARCHAR(150) NULL,
  PRIMARY KEY (id),
  KEY idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── pendientes ────────────────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 020 agrega usuario_id NOT NULL + FK
-- a usuarios — el propio 020 dice "la tabla se vació en esta misma sesión
-- así que no hace falta backfill, usuario_id puede ir NOT NULL desde ya",
-- confirmando que antes de esa migración no tenía dueño; 021 agrega
-- completado; 022 agrega completado_en). Columnas base inferidas de
-- PendienteController.php: `INSERT INTO pendientes (usuario_id, titulo,
-- meta)` (post-020) y de HomeController.php: `SELECT id, titulo, meta,
-- color, completado FROM pendientes` — `color` es la única columna base
-- que no se documenta en ningún ALTER, así que ya existía desde el inicio
-- (valor por defecto en PHP: `$r['color'] ?: '#9e1f63'`, un hex).
-- AMBIGÜEDAD PARA REVISAR: tamaño de `color` (VARCHAR(20) es holgado para
-- un hex de 7 caracteres, pero no hay evidencia de que sea siempre hex).
CREATE TABLE IF NOT EXISTS pendientes (
  id      INT NOT NULL AUTO_INCREMENT,
  titulo  VARCHAR(150) NOT NULL,
  meta    VARCHAR(150) NULL,
  color   VARCHAR(20) NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── noticias ──────────────────────────────────────────────────────────
-- Nunca creada ni tocada en 001-034 (solo se lee). Columnas inferidas de
-- HomeController.php: `SELECT categoria, titulo, fecha FROM noticias ORDER
-- BY fecha DESC` y del mismo bloque en PortalController.php (módulo
-- Novedades, misma consulta).
CREATE TABLE IF NOT EXISTS noticias (
  id         INT NOT NULL AUTO_INCREMENT,
  categoria  VARCHAR(60) NOT NULL,
  titulo     VARCHAR(200) NOT NULL,
  fecha      DATE NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── documentos ────────────────────────────────────────────────────────
-- Tabla real y DISTINTA de `archivos_documentales` — no es un falso
-- positivo. Nunca creada ni tocada en 001-034 (solo se lee). Columnas
-- inferidas de HomeController.php ("Documentos recientes" del dashboard de
-- Inicio): `SELECT nombre, area, version, fecha, estado FROM documentos
-- ORDER BY fecha DESC`, con estado mapeado en PHP a
-- ['Vigente'=>'success','En revisión'=>'warning','Obsoleto'=>'danger'].
CREATE TABLE IF NOT EXISTS documentos (
  id       INT NOT NULL AUTO_INCREMENT,
  nombre   VARCHAR(200) NOT NULL,
  area     VARCHAR(100) NOT NULL,
  version  VARCHAR(20) NOT NULL,
  fecha    DATE NOT NULL,
  estado   ENUM('Vigente','En revisión','Obsoleto') NOT NULL DEFAULT 'Vigente',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── cumpleanos ────────────────────────────────────────────────────────
-- Nunca creada ni tocada en 001-034 (solo se lee). Columnas inferidas de
-- HomeController.php: `SELECT nombre, fecha FROM cumpleanos ORDER BY
-- fecha` — el año de `fecha` se ignora en PHP (`$fecha->setDate($añoActual,
-- $mes, $dia)`), así que la columna guarda una fecha real aunque solo
-- importen mes/día.
CREATE TABLE IF NOT EXISTS cumpleanos (
  id      INT NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(150) NOT NULL,
  fecha   DATE NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── kpis_tablero / matricula_facultad / ejecucion_presupuestal /
--    alertas_indicador ────────────────────────────────────────────────
-- Las 4 son del bloque '/cuadro-mando-integral' de PortalController.php —
-- nunca se crean ni se tocan en 001-034, solo se leen:
--   kpis_tablero:          SELECT label, valor, meta, pct FROM kpis_tablero ORDER BY orden
--   matricula_facultad:    SELECT facultad, estudiantes FROM matricula_facultad ORDER BY orden
--   ejecucion_presupuestal:SELECT label, pct FROM ejecucion_presupuestal ORDER BY orden
--   alertas_indicador:     SELECT texto FROM alertas_indicador ORDER BY orden
-- `pct` se modela como TINYINT UNSIGNED (0-100), mismo criterio que ya usa
-- `competencias.pct` (migración 006).
CREATE TABLE IF NOT EXISTS kpis_tablero (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  valor  VARCHAR(50) NOT NULL,
  meta   VARCHAR(50) NULL,
  pct    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS matricula_facultad (
  id           INT NOT NULL AUTO_INCREMENT,
  facultad     VARCHAR(150) NOT NULL,
  estudiantes  INT UNSIGNED NOT NULL DEFAULT 0,
  orden        INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ejecucion_presupuestal (
  id     INT NOT NULL AUTO_INCREMENT,
  label  VARCHAR(100) NOT NULL,
  pct    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alertas_indicador (
  id     INT NOT NULL AUTO_INCREMENT,
  texto  VARCHAR(255) NOT NULL,
  orden  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── carpetas_documentales ─────────────────────────────────────────────
-- Nunca creada ni tocada en 001-034 (solo se lee). Árbol de 2 niveles
-- (dirección → área) descrito literalmente en el comentario de
-- PortalController.php ('/gestion-documental'): "carpetas_documentales es
-- un árbol de 2 niveles (dirección → área, vía parent_id); los archivos
-- cuelgan del nivel de área (hoja), nunca del nivel de dirección" —
-- también referenciada al pasar en el comentario de la migración 027
-- ("a diferencia de carpetas_documentales, que usa Gestión Documental,
-- está fija a 2 niveles y no tiene direccion_id"). Columnas inferidas de
-- las dos consultas de PortalController.php: `SELECT id, parent_id, label
-- FROM carpetas_documentales WHERE parent_id IS NOT NULL ORDER BY orden` y
-- `... WHERE parent_id IS NULL ORDER BY orden`.
CREATE TABLE IF NOT EXISTS carpetas_documentales (
  id         INT NOT NULL AUTO_INCREMENT,
  parent_id  INT NULL,
  label      VARCHAR(150) NOT NULL,
  orden      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
  CONSTRAINT carpetas_documentales_parent_fk FOREIGN KEY (parent_id) REFERENCES carpetas_documentales (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── archivos_documentales ─────────────────────────────────────────────
-- Nunca creada en 001-034 (solo ALTER: 008/028 agregan/reparan `archivo`).
-- Columnas base inferidas de PortalController.php: `SELECT id, carpeta_id,
-- nombre, tipo, version, estado, responsable, archivo, fecha FROM
-- archivos_documentales ORDER BY fecha DESC` (archivo se excluye, lo
-- agrega 008) y de DocumentoController.php: `INSERT INTO
-- archivos_documentales (carpeta_id, nombre, tipo, version, estado,
-- responsable, fecha)`. El comentario de 008 confirma que ya existía con
-- datos reales antes de esa migración: "las 14 filas existentes siguen sin
-- archivo hasta que alguien lo suba" (esas 14 filas no se reconstruyen acá
-- — no hay forma de recuperar su contenido real).
-- `carpeta_id` referencia carpetas_documentales.id (confirmado por el
-- agrupamiento `$archivosPorCarpeta[$a['carpeta_id']]` contra los ids de
-- carpetas_documentales en el bloque '/gestion-documental' de
-- PortalController.php).
-- AMBIGÜEDAD PARA REVISAR: DocumentoController.php, en /documentos/crear,
-- valida el `carpeta_id` recibido del formulario contra la tabla
-- `direccion_areas` (`SELECT id FROM direccion_areas WHERE id = :id`) en
-- vez de `carpetas_documentales` antes de insertar en archivos_documentales
-- — parece una inconsistencia ya existente en el controlador (no algo que
-- este archivo deba resolver), pero puede hacer que un INSERT válido según
-- esa validación viole la FOREIGN KEY de abajo si el id no existe también
-- en carpetas_documentales. Vale la pena que el equipo lo revise.
CREATE TABLE IF NOT EXISTS archivos_documentales (
  id           INT NOT NULL AUTO_INCREMENT,
  carpeta_id   INT NOT NULL,
  nombre       VARCHAR(200) NOT NULL,
  tipo         VARCHAR(60) NOT NULL,
  version      VARCHAR(20) NOT NULL,
  estado       ENUM('Vigente','En revisión','Obsoleto') NOT NULL DEFAULT 'Vigente',
  responsable  VARCHAR(150) NULL,
  fecha        DATE NOT NULL,
  PRIMARY KEY (id),
  KEY carpeta_id (carpeta_id),
  CONSTRAINT archivos_documentales_carpeta_fk FOREIGN KEY (carpeta_id) REFERENCES carpetas_documentales (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
