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

## Commit "ac13794 - mejoras" en feature/santiago
- Contiene solo `package-lock.json` e `installed.php` (artefactos generados, ya ignorados por `.gitignore`)
- Pendiente: decidir si se descarta con rebase antes de publicar o se deja tal cual

## Última actualización

2026-09-17 (Problema 3 verificado y cerrado)