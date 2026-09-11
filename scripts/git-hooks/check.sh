#!/usr/bin/env bash
# Chequeo rápido que corre solo después de un `git pull` o un cambio de
# rama (ver post-merge y post-checkout en esta misma carpeta). Nunca
# bloquea nada — el pull/checkout ya pasó — solo avisa de las causas más
# comunes de "en mi máquina funciona" entre Windows y Linux, y de
# configuración que falta por ser propia de cada máquina.
#
# Uso manual (para probarlo o correrlo a mano):
#   scripts/git-hooks/check.sh [ref-anterior]
# Si no se pasa ref-anterior, revisa la sintaxis de todo el PHP del repo
# en vez de solo lo que cambió.

set -uo pipefail
cd "$(git rev-parse --show-toplevel 2>/dev/null)" || exit 0

RED='\033[0;31m'; YELLOW='\033[0;33m'; GREEN='\033[0;32m'; NC='\033[0m'
problemas=0

PHP_BIN=""
if command -v php >/dev/null 2>&1; then
  PHP_BIN="$(command -v php)"
elif [[ -x /c/xampp/php/php.exe ]]; then
  PHP_BIN="/c/xampp/php/php.exe"
fi

echo ""
echo "── Chequeo post-pull/checkout — Portal Core ──"

# 1) config/local.php — está en .gitignore a propósito (URL base propia de
#    cada máquina), así que un pull/clone nuevo nunca lo trae solo.
if [[ ! -f config/local.php ]]; then
  echo -e "${YELLOW}⚠${NC} Falta config/local.php — copia config/local.php.example y ajústalo (ver docs/entorno-local.md)."
  problemas=$((problemas + 1))
else
  echo -e "${GREEN}✓${NC} config/local.php existe"
fi

# 1b) config/database.local.php — mismo caso: credenciales de BD propias de
#    esta máquina, gitignoreadas a propósito (ver docs/windows-linux.md).
if [[ ! -f config/database.local.php ]]; then
  echo -e "${YELLOW}⚠${NC} Falta config/database.local.php — copia config/database.local.php.example y ajústalo, si no ninguna página con BD va a conectar."
  problemas=$((problemas + 1))
else
  echo -e "${GREEN}✓${NC} config/database.local.php existe"
fi

# 2) Sintaxis PHP — de lo que trajo el pull, o de todo si se corre a mano.
ref_anterior="${1:-}"
if [[ -n "$ref_anterior" ]] && git rev-parse --verify -q "$ref_anterior" >/dev/null; then
  archivos_php=$(git diff --name-only "$ref_anterior" HEAD -- '*.php' 2>/dev/null)
else
  archivos_php=$(git ls-files '*.php')
fi

if [[ -n "$archivos_php" ]]; then
  fallo_sintaxis=0
  total=0
  if [[ -z "$PHP_BIN" ]]; then
    echo -e "${YELLOW}⚠${NC} No se encontró PHP en el PATH ni en /c/xampp/php/php.exe; se omitió el chequeo de sintaxis."
  else
    while IFS= read -r f; do
      [[ -f "$f" ]] || continue
      total=$((total + 1))
      salida=$("$PHP_BIN" -l "$f" 2>&1)
      if [[ $? -ne 0 ]]; then
        echo -e "${RED}✗${NC} Error de sintaxis en $f"
        echo "$salida" | sed 's/^/    /'
        fallo_sintaxis=1
      fi
    done <<< "$archivos_php"
  fi
  if [[ $fallo_sintaxis -eq 0 ]]; then
    echo -e "${GREEN}✓${NC} Sintaxis PHP OK ($total archivo(s))"
  else
    problemas=$((problemas + 1))
  fi
fi

# 3) Mayúsculas/minúsculas en las rutas de módulo de PortalController.php —
#    funciona igual en Windows aunque el case no coincida; en Linux (donde
#    corre el servidor real) revienta con "No such file or directory".
if [[ -n "$PHP_BIN" ]] && [[ -f app/Controllers/PortalController.php ]]; then
  malos=$("$PHP_BIN" -r '
    $c = file_get_contents("app/Controllers/PortalController.php");
    preg_match_all("/\x27vista\x27\s*=>\s*\x27([^\x27]+)\x27/", $c, $m);
    $malos = [];
    foreach ($m[1] as $v) {
      if (!is_file("app/Views/Portal/" . $v)) $malos[] = $v;
    }
    echo implode("\n", $malos);
  ' 2>/dev/null)
  if [[ -n "$malos" ]]; then
    echo -e "${RED}✗${NC} Rutas de módulo que NO existen con ese case exacto (van a fallar en Linux):"
    echo "$malos" | sed 's/^/    /'
    problemas=$((problemas + 1))
  else
    echo -e "${GREEN}✓${NC} Rutas de módulos (PortalController.php) coinciden con el disco"
  fi
fi

# 4) ACL de Apache sobre public/uploads — el gotcha real ya documentado en
#    docs/entorno-local.md (un chmod posterior puede anular la mask del ACL
#    y dejar a www-data sin lectura aunque el archivo exista).
if command -v getfacl >/dev/null 2>&1 && [[ -d public/uploads ]]; then
  if getfacl -p public/uploads 2>/dev/null | grep -q "effective:---"; then
    echo -e "${YELLOW}⚠${NC} El ACL de www-data en public/uploads parece anulado — corre:"
    echo "    setfacl -R -m u:www-data:rX \"$(pwd)\""
    problemas=$((problemas + 1))
  else
    echo -e "${GREEN}✓${NC} ACL de public/uploads OK"
  fi
fi

echo ""
if [[ $problemas -gt 0 ]]; then
  echo -e "${YELLOW}${problemas} cosa(s) para revisar arriba.${NC} No se deshizo nada — solo es un aviso."
else
  echo -e "${GREEN}Todo OK.${NC}"
fi
echo ""

exit 0
