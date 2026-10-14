#!/usr/bin/env bash
# PostToolUse : lint rapide des fichiers modifiés dans les conteneurs Docker (Claude Code et Codex).
# Erreur -> {"decision":"block","reason":...} : le message revient à l'agent.
# Pose aussi le marqueur .agents/.needs-check, effacé par un `make check` réussi.
set -u
source "$(dirname "$0")/lib.sh"
read_payload
root=$(project_root)
cd "$root" || exit 0

running() { docker compose ps --status running --services 2>/dev/null | grep -qx "$1"; }
errors=""

while IFS= read -r rel; do
  [ -f "$rel" ] || continue
  case "$rel" in
    src/*.php|tests/*.php|config/*.php|migrations/*.php)
      touch .agents/.needs-check
      running php || continue
      out=$(docker compose exec -T php php -l "$rel" 2>&1) || errors+="php -l a échoué sur $rel :"$'\n'"$out"$'\n'
      ;;
    front/src/*.vue|front/src/*.js|front/src/*.ts)
      touch .agents/.needs-check
      running front || continue
      out=$(docker compose exec -T front npx eslint --quiet "${rel#front/}" 2>&1) || errors+="ESLint a trouvé des erreurs dans $rel :"$'\n'"$out"$'\n'
      ;;
    services/*)
      # Micro-service Node : ses tests tournent dans `make check` (make test-qcm-results)
      touch .agents/.needs-check
      ;;
    config/*|.github/*|compose*.yaml|Dockerfile|nginx*.conf|front/vite.config.js|front/package.json|composer.json)
      touch .agents/.needs-check
      ;;
  esac
done < <(target_files "$root")

[ -n "$errors" ] && jq -n --arg r "$errors" '{decision:"block", reason:$r}'
exit 0
