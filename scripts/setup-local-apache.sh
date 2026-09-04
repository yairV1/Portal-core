#!/usr/bin/env bash
# Configura Apache para servir Portal-core desde la carpeta de trabajo real
# (sin copias) en http://portal-core.local
#
# Uso:  sudo bash scripts/setup-local-apache.sh
#
# Qué hace, en orden:
#   1. Habilita mod_rewrite (requerido por public/.htaccess)
#   2. Crea el VirtualHost portal-core.conf apuntando a public/
#   3. Habilita ese sitio
#   4. Agrega portal-core.local a /etc/hosts (si no existe ya)
#   5. Da acceso de solo lectura/tránsito a www-data sobre esta carpeta
#      vía ACL POSIX (setfacl), sin tocar el resto de /home/yair
#   6. Recarga Apache
#
# Es re-ejecutable: si algo ya está hecho, lo detecta y lo salta.

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
  echo "Este script necesita sudo. Uso: sudo bash $0" >&2
  exit 1
fi

PROJECT_USER="yair"
PROJECT_ROOT="/home/${PROJECT_USER}/Proyecto-cor/Portal-core"
DOCROOT="${PROJECT_ROOT}/public"
DOMAIN="portal-core.local"
VHOST_FILE="/etc/apache2/sites-available/portal-core.conf"
APACHE_USER="www-data"

if [[ ! -f "${DOCROOT}/index.php" ]]; then
  echo "No encuentro ${DOCROOT}/index.php — revisa PROJECT_ROOT en este script." >&2
  exit 1
fi

echo "==> 1) Habilitando mod_rewrite"
a2enmod rewrite

echo "==> 2) Escribiendo VirtualHost en ${VHOST_FILE}"
cat > "${VHOST_FILE}" <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${DOCROOT}

    <Directory ${DOCROOT}>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/portal-core-error.log
    CustomLog \${APACHE_LOG_DIR}/portal-core-access.log combined
</VirtualHost>
EOF

echo "==> 3) Habilitando el sitio portal-core"
a2ensite portal-core.conf

echo "==> 4) Agregando ${DOMAIN} a /etc/hosts"
if grep -qE "[[:space:]]${DOMAIN}([[:space:]]|$)" /etc/hosts; then
  echo "    ya existe, no se toca"
else
  printf '127.0.0.1\t%s\n' "${DOMAIN}" >> /etc/hosts
fi

echo "==> 5) ACL: acceso de ${APACHE_USER} solo a esta carpeta"
# Tránsito (x sin lectura) por las carpetas padre, para no exponer su listado
setfacl -m "u:${APACHE_USER}:x" "/home/${PROJECT_USER}"
setfacl -m "u:${APACHE_USER}:x" "/home/${PROJECT_USER}/Proyecto-cor"
# Lectura + tránsito recursivo dentro del proyecto (rX: r a archivos, x solo a dirs/ejecutables)
setfacl -R -m "u:${APACHE_USER}:rX" "${PROJECT_ROOT}"
# ACL por defecto: los archivos NUEVOS que crees también quedan legibles por Apache
setfacl -R -d -m "u:${APACHE_USER}:rX" "${PROJECT_ROOT}"

echo "==> 6) Recargando Apache"
apache2ctl configtest
systemctl reload apache2

echo
echo "Listo. Abre: http://${DOMAIN}/login"
