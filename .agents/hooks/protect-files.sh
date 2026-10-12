#!/usr/bin/env bash
# PreToolUse : bloque l'écriture dans les fichiers protégés (Claude Code et Codex).
set -u
source "$(dirname "$0")/lib.sh"
read_payload
root=$(project_root)

deny() {
  jq -n --arg r "$1" '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$r}}'
  exit 0
}

while IFS= read -r rel; do
  case "$rel" in
    .env|.env.local|.env.prod|.env.*.local|.env.local.php)
      deny "Fichier d'environnement réel ($rel) : modification interdite. Utiliser .env.local.example ou demander à l'utilisateur." ;;
    VAE/result_analyse_VAE.md)
      deny "Analyse VAE historique figée : ne pas modifier. Mettre à jour VAE/rapport-cda.md ou VAE/preuves.md." ;;
    VAE/evidence/*)
      deny "Preuves VAE immuables : un nouveau run crée un nouveau dossier, on ne modifie pas un existant." ;;
    migrations/*.php)
      if git -C "$root" ls-files --error-unmatch "$rel" >/dev/null 2>&1; then
        deny "Migration déjà commitée ($rel) : ne pas la modifier, créer une nouvelle migration (doctrine:migrations:diff)."
      fi ;;
  esac
done < <(target_files "$root")
exit 0
