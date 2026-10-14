#!/usr/bin/env bash
# Stop : si du code a été modifié depuis le dernier `make check` réussi, demander à l'agent de valider.
# Le marqueur .agents/.needs-check est posé par lint-file.sh et supprimé par `make check` en cas de succès.
set -u
source "$(dirname "$0")/lib.sh"
read_payload
root=$(project_root)
[ "$(jq -r '.stop_hook_active // false' <<<"$PAYLOAD")" = "true" ] && exit 0
[ -f "$root/.agents/.needs-check" ] || exit 0

jq -n '{decision:"block", reason:"Du code a été modifié depuis le dernier `make check` réussi. Lance `make check` et rapporte le résultat (échecs préexistants compris). Si la tâche est terminée, reporte-la dans la documentation VitePress (docs/) si elle change une fonctionnalité, une API, les données, l’architecture, les tests, la CI ou le déploiement, puis lance `make docs-build` ; si elle touche aussi la preuve VAE, applique le skill vae-documentation-sync. Si la tâche est volontairement intermédiaire, dis-le explicitement à l’utilisateur."}'
exit 0
