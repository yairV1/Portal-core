# Docker

## Primera vez

1. Crea la configuración local:

   ```sh
   cp .env.example .env
   ```

2. Cambia `DB_PASS` y `DB_ROOT_PASS` por valores propios en `.env`.
3. Levanta el entorno de desarrollo:

   ```sh
   docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build
   ```

La aplicación queda en `http://localhost:8080` y phpMyAdmin en
`http://localhost:8081` (o en los puertos configurados en `.env`). MySQL
ejecuta automáticamente las migraciones numeradas al crear por primera vez el
volumen `db_data`. Actualmente el repositorio contiene migraciones `001` a
`019`, que se ejecutan en orden; las posteriores a `017` también forman parte
del esquema actual.

## Comandos del día a día

```sh
# Levantar en segundo plano
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Ver logs
docker compose logs -f app

# Detener servicios (conserva datos)
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```

El código fuente se monta con el overlay de desarrollo. Los datos de
`storage/` y `public/uploads/` quedan fuera de la imagen y sobreviven a sus
rebuilds.

## Ejecutar una migración nueva

Las migraciones nuevas no se ejecutan automáticamente sobre un `db_data` que
ya existe. Después de crear, por ejemplo,
`database/migrations/020_nueva_migracion.sql`, ejecútala desde la raíz del
proyecto:

```sh
set -a
. ./.env
set +a
docker compose exec -T db mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  < database/migrations/020_nueva_migracion.sql
```

## Producción

En producción no uses el overlay de desarrollo: el código queda horneado en la
imagen. Levanta la composición base reconstruyendo la imagen:

```sh
docker compose up -d --build
```

Configura `.env` con credenciales seguras y conserva el volumen `db_data` y
los montajes de `storage/` y `public/uploads/`.
