# Entorno local — cómo ver el proyecto en el navegador

## Ruta del proyecto

`/home/yair/Proyecto-cor/Portal-core` (= `~/Proyecto-cor/Portal-core`).
Esta es la carpeta de trabajo real; nada de lo de abajo copia archivos,
todo lee directo de aquí.

## Cómo se sirve

- Apache 2.4.58 ya estaba instalado y corriendo, con `php_module` cargado (PHP 8.3.6).
- `DocumentRoot` = `Portal-core/public/` (ahí vive `index.php`, el único punto de
  entrada). El resto del proyecto (`app/`, `config/`, `routes/`) NO es accesible
  directo por URL — solo vía los `require` de `index.php`. Esto es intencional y
  no debe cambiarse.
- No se usa symlink: el `VirtualHost` apunta con `DocumentRoot` directo a la ruta
  real, así que cualquier archivo que guardes se ve al recargar el navegador.

## Cómo se configuró

Todo lo de abajo lo hace `scripts/setup-local-apache.sh` (re-ejecutable, detecta
lo que ya está hecho). Para aplicarlo o volver a aplicarlo tras reinstalar Apache:

```bash
sudo bash scripts/setup-local-apache.sh
```

Pasos que hace el script:

1. **`a2enmod rewrite`** — el proyecto usa `public/.htaccess` para URLs limpias
   (`/login`, `/tableros`, etc.), así que `mod_rewrite` es obligatorio.
2. Crea `/etc/apache2/sites-available/portal-core.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName portal-core.local
       DocumentRoot /home/yair/Proyecto-cor/Portal-core/public

       <Directory /home/yair/Proyecto-cor/Portal-core/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/portal-core-error.log
       CustomLog ${APACHE_LOG_DIR}/portal-core-access.log combined
   </VirtualHost>
   ```
3. `a2ensite portal-core.conf`.
4. Agrega a `/etc/hosts`: `127.0.0.1  portal-core.local`.
5. **Permisos vía ACL (no vía `chmod` global):** Apache corre como `www-data`,
   que no es dueño ni pertenece al grupo `yair`. `/home/yair` es `750`
   (nadie fuera del dueño/grupo puede ni entrar), así que sin esto Apache no
   puede llegar a `public/`.
   En vez de abrir `/home/yair` a "otros" (lo que expondría el resto de tus
   carpetas personales), se usan ACLs POSIX para dar acceso *solo* a
   `www-data` y *solo* donde hace falta:
   - `x` (sin lectura) en `/home/yair` y `/home/yair/Proyecto-cor` — permite
     atravesar esas carpetas pero no listar su contenido.
   - `rX` recursivo dentro de `Portal-core/` — lectura de archivos y tránsito
     de subcarpetas, solo dentro del proyecto.
   - ACL por defecto (`-d`) en `Portal-core/` para que archivos **nuevos**
     que crees también queden legibles por Apache automáticamente.

   Verificar en cualquier momento: `getfacl /home/yair/Proyecto-cor/Portal-core`

6. `systemctl reload apache2`.

## URL

**http://portal-core.local/login**

## ⚠️ Un archivo nuevo puede quedar invisible para Apache (404 / imagen rota)

Pasó con `public/uploads/logo/logo-core.jpg`: el ACL por defecto le daba lectura
a `www-data`, pero **algo le hizo `chmod` al archivo después de crearlo**
(guardado de un editor, subida por FTP, etc.), y en Linux un `chmod` posterior
**sincroniza automáticamente la `mask` del ACL con los bits de grupo del modo
clásico** — si esos bits quedan en 0, la `mask` pasa a `---` y anula el
permiso de `www-data` aunque su entrada siga diciendo `r-x`
(`getfacl` lo muestra como `effective:---`).

Sintoma: la ruta es correcta, el archivo existe, pero el navegador igual da
404 o la imagen no carga.

Diagnóstico rápido:
```bash
getfacl -p ruta/al/archivo
# si ves algo como:
#   user:www-data:r-x   #effective:---
# la mask está anulando el permiso.
```

Arreglo (no necesita sudo, el archivo es tuyo):
```bash
setfacl -R -m u:www-data:rX /home/yair/Proyecto-cor/Portal-core
```
Esto re-aplica el ACL y recalcula la `mask` en todo el proyecto — seguro
de repetir cuando quieras, y buena primera prueba si algo "no carga" sin
error visible en el código.

## Pendiente conocido (no configurado en esta tarea)

- **MySQL/MariaDB no está corriendo** (`mysql.service` en `failed`,
  `mariadb.service` en `inactive`). `config/database.php` intenta conectar en
  cada request; sin el servicio arriba (y sin que exista la base `portal_core`
  con el usuario `portal_user`), cualquier ruta va a mostrar
  `Error de conexión a la base de datos...` en vez de la vista real. El
  enrutamiento y el `.htaccess` funcionan igual; esto es un problema aparte,
  de base de datos, no de Apache.

## Si algo se rompe / cómo deshacerlo

- Deshabilitar el sitio: `sudo a2dissite portal-core.conf && sudo systemctl reload apache2`
- Quitar el ACL: `setfacl -R -b /home/yair/Proyecto-cor/Portal-core`
  (y si hace falta, `setfacl -x u:www-data /home/yair /home/yair/Proyecto-cor`)
- Quitar la entrada de `/etc/hosts`: borrar a mano la línea `portal-core.local`.
- Ver errores reales de Apache para este sitio:
  `sudo tail -f /var/log/apache2/portal-core-error.log`
