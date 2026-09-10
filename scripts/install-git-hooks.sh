#!/usr/bin/env bash
# Actívalo UNA VEZ por máquina (no viaja solo con el pull — git nunca
# ejecuta hooks de un repo automáticamente por seguridad, hay que optar por
# esto a propósito, una sola vez):
#
#   bash scripts/install-git-hooks.sh
#
# A partir de ahí, scripts/git-hooks/check.sh corre solo después de cada
# `git pull` y cada cambio de rama en ESTA máquina. Ver docs/windows-linux.md.
set -euo pipefail
cd "$(git rev-parse --show-toplevel)"

chmod +x scripts/git-hooks/post-merge scripts/git-hooks/post-checkout scripts/git-hooks/check.sh
git config core.hooksPath scripts/git-hooks

echo "Listo. scripts/git-hooks quedó activo para este repo (core.hooksPath)."
echo "A partir de ahora corre solo en cada 'git pull' y cada cambio de rama."
