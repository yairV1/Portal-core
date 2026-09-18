#!/usr/bin/env bash
# PreToolUse hook, filtered to `git commit*` via the hook's "if" rule.
# Soft reminder only — never blocks the commit.
set -euo pipefail

jq -n '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"Recordatorio Portal-core: este repo no tiene CI ni entorno de test separado. Si tocaste app/Controllers, app/Views, app/Helpers, routes/web.php, database/migrations o CSS/JS de diseño en esta sesión, corre /code-review (o al menos los subagentes portal-qa-verifier / regression-hunter / security-reviewer / design-consistency-guardian según corresponda) antes de comitear, salvo que ya lo hayas hecho en este turno."}}'
