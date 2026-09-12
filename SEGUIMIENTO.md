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
- Estado: UPDATE preparados, pendiente de ejecutar en base de datos
- `UPDATE nav_items SET label = 'Planeación' WHERE id = 17;`
- `UPDATE nav_items SET label = 'Vicerrectoría Académica' WHERE id = 18;`
- `UPDATE nav_items SET label = 'Dirección de Investigación' WHERE id = 19;`
- Pendiente: confirmar si la base local de Santiago también tiene estos registros corruptos

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

2026-09-12