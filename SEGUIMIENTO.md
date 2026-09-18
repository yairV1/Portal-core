# Seguimiento de incidencias

## Problema 1: Bug cross-platform Windows/Linux (tildes en carpetas)
- Estado: RESUELTO, pendiente de Pull Request
- Commit: `c1d07d6` - "Fix: renombrar carpetas de vistas sin tildes"
- Rama: `feature/santiago` (pendiente de push)
- Carpetas renombradas: `Gestion_Documental`, `Gestion_Institucional`, `Investigacion_Innovacion`, `Sistema_Gestion_Integral`, `Tablero_Estrategicos`, `Vicerrectoria_Academica`
- Archivos actualizados: `PortalController.php`, `DocumentoController.php`, `Untitled-1.md`, `012_direccion_documentos_resto.sql`, `gestion-institucional.js`
- Pendiente: abrir PR hacia `main`, que el compañero (rama `feature/Yair`) descarte su intento de rename roto y haga pull después del merge

## Problema 2: Migración 000_usuarios.sql desactualizada
- Estado: CORREGIDO localmente, sin commitear (GRUPO B)
- Se agregó columna `creado_en`, se cambió `rol` a `ENUM('admin','usuario')`, se ajustaron tamaños de `VARCHAR` según el esquema real en producción
- Pendiente: revisar si la migración 004 (perfil_foto) necesita ajustarse para no duplicar la columna `foto` si alguien corre las migraciones desde cero

## Problema 3: Datos corruptos (mojibake) en tabla nav_items
- Estado: RESUELTO — pero no por los UPDATE que se habían preparado acá
- Verificado 2026-09-17 contra el contenedor Docker real: los ids 17/18/19 de
  esta tabla **ya no existen** — `nav_items` fue reconstruida desde cero por
  la migración `035_reconstruir_nav_items_faltantes.sql` (posterior a este
  documento), que reinsertó todo el sidebar con ids nuevos y texto limpio.
  Los 3 UPDATE de acá apuntaban a ids de un esquema que ya no existe;
  ejecutarlos hoy no habría tocado ninguna fila.
- Confirmado también que el texto que preocupaba (Planeación / Vicerrectoría
  Académica / Dirección de Investigación) ya vive, sin mojibake, tanto en la
  migración 035 (ids 9-11) como en la tabla `direcciones` (ids 8-10, de la
  migración `026_cmi_perspectivas.sql`) — no hace falta ningún UPDATE nuevo.
- **Hallazgo nuevo, sin relación con el mojibake:** en el contenedor Docker
  usado para esta verificación, la migración 035 en sí **nunca se aplicó**
  — `nav_items` solo tiene 5 filas (ids 1, 2, 6, 23, 24) en vez de las 21 que
  035 sembraría, y falta la sección `nav_secciones` id=2 ("Direcciones").
  En la práctica, ese entorno tiene el sidebar incompleto (sin Direcciones,
  Gestión Documental, Normatividad, Novedades, Aplicaciones, Mapa del portal
  ni Cuadro de Mando Integral) aunque migraciones posteriores (036, 040,
  041-043) sí están aplicadas — 035 quedó saltada al aplicar el resto a
  mano.
- **Actualización 2026-09-17 — migración 035 aplicada en este contenedor:**
  no se pudo correr el archivo tal cual por dos huecos previos de este mismo
  volumen, ninguno causado por 035: (1) `nav_secciones` le faltaba la fila
  `id=2` ('Direcciones') que `000_esquema_base.sql` ya define — sin ella,
  cualquier `nav_items` con `seccion_id=2` viola la FK
  `nav_items_seccion_fk`; (2) `nav_items` ya tenía la fila `id=6` (Inicio)
  idéntica a la que 035 vuelve a insertar, lo que habría violado la PK. Se
  aplicó un SQL adaptado (mismo contenido literal de 035, solo omitiendo lo
  ya existente) directo contra el contenedor — el archivo de migración en
  el repo no se tocó. Resultado verificado por conteo antes/después:
  `nav_items` 5→20 filas, `nav_secciones` 2→3 filas; el sidebar completo
  (Direcciones con sus 6 direcciones, Gestión Documental, Normatividad,
  Novedades, Aplicaciones, Mapa del portal, Cuadro de Mando Integral con
  sus 3 perspectivas) ya está confirmado por consulta directa a la tabla.
- **Corrección 2026-09-18 — la afirmación de arriba ("sin mojibake... en la
  tabla `direcciones`") era falsa, y el SQL adaptado sí introdujo mojibake
  nuevo.** Causa real confirmada por evidencia: el cliente `mysql` dentro
  de este contenedor usa **latin1 por defecto** cuando no se le pasa
  `--default-character-set=utf8mb4` (`SHOW SESSION VARIABLES LIKE
  'character_set_%'` lo confirma: `character_set_client/connection/results
  = latin1`, aunque la tabla es `utf8mb4`). El comando usado para aplicar
  el SQL adaptado (`docker compose exec -T db mysql -u... -p... db <
  archivo.sql`) no forzaba ese charset, así que cada tilde/ñ que mandé (ya
  bien codificada en UTF-8 en el archivo) se reinterpretó como latin1 y se
  volvió a codificar como UTF-8 al guardar — doble codificación clásica.
  Confirmado por bytes (`HEX(label)` mostraba `C383C2AD` en vez de `C3AD`
  para una í, etc.).
  La razón por la que no se detectó antes: **leer con el mismo cliente sin
  charset cancela el error visualmente** — la misma reinterpretación
  latin1↔utf8 ocurre también al mostrar el resultado, así que en la
  terminal el texto se veía perfecto aunque los bytes guardados estuvieran
  mal. Por eso la verificación anterior de `direcciones` (y de las filas ya
  existentes `nav_items.id=23/24`, de las migraciones 038/039, que
  **tampoco fueron tocadas por este SQL adaptado** y ya tenían el mismo
  problema desde antes) pasó por buena sin serlo.
  **Corregido en `nav_items`** (10 filas, con `--default-character-set=utf8mb4`
  y `SET NAMES utf8mb4;` esta vez): ids 9 (Planeación), 10 y 14
  (Vicerrectoría Académica), 11 (Dirección de Investigación), 12 (Gestión
  Institucional), 13 (Sistema de Gestión Integral), 17 (Investigación e
  Innovación), 18 (Gestión Documental), 23 (Administración), 24 (Todos los
  módulos). Verificado por `HEX(label)` después del fix: todas quedan como
  UTF-8 de un solo byte-par por carácter (ej. `C3AD`=í, `C3B3`=ó, `C3A9`=é),
  y `SHOW CREATE TABLE nav_items` confirma `CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci` — la columna nunca fue el problema, solo la
  conexión usada para escribir.

## Problema 4: Archivos sin trackear (GRUPO B)
- `config/local.php.example` (modificado)
- `.htaccess` (nuevo) - pendiente decidir si va al repo o a `.gitignore` según si el compañero usa Docker o XAMPP
- `index.php` (nuevo) - mismo caso
- `database/migrations/000_usuarios.sql` (ver Problema 2)

## Problema 5: Trabajo paralelo sin integrar en feature/Yair
- `app/Helpers/GoogleDrive.php` (nuevo)
- `database/migrations/031` y `032` (nuevas)
- `storage/google/` (contiene credencial, YA CONFIRMADO ignorada por `.gitignore`, nunca commiteada - sin riesgo de exposición)
- `docker-compose.yml` modificado
- Pendiente: revisar e integrar cuando el compañero decida

## Problema 6: Mojibake sistémico más allá de nav_items
- Estado: **RESUELTO 2026-09-18** — barrido completo del esquema, no solo
  las tablas que se habían visto a simple vista.
- **Método de detección** (no ojo por ojo): se listaron las 138 columnas
  `varchar`/`text`/`char` de las 46 tablas del esquema
  (`information_schema.COLUMNS`) y se buscó en cada una el patrón de bytes
  de la doble codificación UTF-8→latin1→UTF-8, comparando por
  `HEX(columna) LIKE '%C383%'` (Ã) y `LIKE '%C382%'` (Â, para "·" y
  similares) — **no** con `LIKE '%Ã%'` normal, porque un primer intento con
  eso dio falsos positivos masivos (`nav_items.slug`, `usuarios.password_hash`,
  etc. — la collation `utf8mb4_unicode_ci` trata varias vocales acentuadas
  como equivalentes a su base sin tilde para efectos de comparación, así
  que cualquier palabra con una simple "a" ya "matcheaba" `%Ã%`). Comparar
  por `HEX()` evita ese problema de raíz al comparar bytes, no caracteres
  bajo una collation.
- **Tablas/columnas con mojibake real, confirmadas por HEX antes/después y
  corregidas con `--default-character-set=utf8mb4` + `SET NAMES utf8mb4;`:**
  - `cargos`: `cargo` (3 filas), `nombre` (3), `direccion` (4)
  - `competencias.label` (2)
  - `organigrama_niveles.label` (1)
  - `organigrama_cajas`: `label` (8), `meta` (6)
  - `direcciones`: `titulo` (7), `descripcion` (4)
  - `direccion_documentos`: `nombre` (13), `tipo` (6)
  - `direccion_responsables`: `nombre` (8), `cargo` (8)
  - `vacantes`: `titulo` (2), `area` (2), `descripcion` (3)
  - Total: **8 tablas, 16 columnas, 76 valores corregidos** (una fila puede
    contar en más de una columna).
- **Tablas/columnas revisadas y confirmadas limpias** (de las priorizadas
  por el usuario): `nav_secciones`, `sitemap_modulos`, `sitemap_items`,
  `landing_secciones`, `landing_modulos`, `landing_roles`, `landing_pasos`,
  `landing_estadisticas`, `manual_funciones`, `direccion_kpis`,
  `direccion_areas` — sin hallazgos. El resto de las 138 columnas del
  esquema (accesos_rapidos, kpis*, eventos, noticias, postulaciones,
  contrataciones, usuarios, etc.) también se escanearon y salieron limpias.
- **Verificación final:** re-escaneo completo de las 138 columnas con el
  mismo método → 0 filas sospechosas en todo el esquema. `SHOW CREATE
  TABLE` confirmado `CHARSET=utf8mb4` en las 8 tablas corregidas (la
  columna nunca fue el problema). Contenido final re-leído vía PDO con
  `charset=utf8mb4` (el mismo mecanismo que usa la app real, no el cliente
  `mysql`) para las 8 tablas — todo el texto se ve correcto.
- **Recomendación para evitar que vuelva a pasar:** cualquier
  `docker compose exec db mysql ...` o `mysql ... < archivo.sql` que
  escriba o lea texto con tildes/ñ debe llevar `--default-character-set=utf8mb4`
  (o `SET NAMES utf8mb4;` al inicio del propio .sql) — vale la pena
  agregarlo a `docs/docker.md` en la sección de "Ejecutar una migración
  nueva", ya que ese procedimiento manual es exactamente donde se originó
  este bug cada vez que se aplicó a mano.

## Commit "ac13794 - mejoras" en feature/santiago
- Contiene solo `package-lock.json` e `installed.php` (artefactos generados, ya ignorados por `.gitignore`)
- Pendiente: decidir si se descarta con rebase antes de publicar o se deja tal cual

## Última actualización

2026-09-18 (Problema 6 resuelto: barrido completo del esquema, 8 tablas / 16 columnas / 76 valores corregidos)