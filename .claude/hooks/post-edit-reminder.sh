#!/usr/bin/env bash
# PostToolUse hook (Edit|Write|MultiEdit). Reads tool_input.file_path from stdin JSON
# and, if it's functional or design-relevant, emits an additionalContext reminder.
set -euo pipefail

f=$(jq -r '.tool_input.file_path // empty')
[ -z "$f" ] && exit 0

if echo "$f" | grep -qE '\.(css|js)$' && echo "$f" | grep -qE 'public/assets/(portal|layouts)/'; then
  msg="Recordatorio Portal-core: $f es CSS/JS de diseño. Antes de dar el cambio por terminado, corre (o invoca) el subagente design-consistency-guardian, o verifícalo tú mismo en el Browser pane en modo claro y oscuro, para confirmar que respeta los tokens de paneles.css (--color-*, --shadow-*, --radius-*, --space-*), la regla de máximo una caja con sombra por pantalla, y que cualquier icono Bootstrap Icons usado existe de verdad en la versión cargada (1.13.1)."
elif echo "$f" | grep -qE '(app/Controllers/|app/Views/|app/Helpers/|routes/web\.php|database/migrations/)'; then
  msg="Recordatorio Portal-core: $f es código funcional. Antes de dar el cambio por terminado, verifícalo de verdad (no solo que compile/parsee) con los subagentes portal-qa-verifier / regression-hunter, o con el método de simulación CLI ya documentado en memoria del proyecto. Si el archivo toca autenticación, permisos, formularios, subida de archivos o consultas a la BD, corre también security-reviewer. No reutilices usuarios.id=1 como conejillo de indias para pruebas de escritura."
else
  exit 0
fi

jq -n --arg msg "$msg" '{"hookSpecificOutput":{"hookEventName":"PostToolUse","additionalContext":$msg}}'
