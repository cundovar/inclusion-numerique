#!/usr/bin/env bash
# Fonctions communes aux hooks Claude Code et Codex.
# Usage : source "$(dirname "$0")/lib.sh" ; puis lire l'entrée JSON avec read_payload.

read_payload() { PAYLOAD=$(cat); }

# Racine du dépôt : variable Claude, sinon racine git depuis le cwd de la session.
project_root() {
  if [ -n "${CLAUDE_PROJECT_DIR:-}" ]; then echo "$CLAUDE_PROJECT_DIR"; return; fi
  local cwd; cwd=$(jq -r '.cwd // empty' <<<"$PAYLOAD"); cwd=${cwd:-$PWD}
  git -C "$cwd" rev-parse --show-toplevel 2>/dev/null || echo "$cwd"
}

# Fichiers ciblés, un par ligne, relatifs à la racine.
# Claude : tool_input.file_path. Codex (apply_patch) : en-têtes du patch dans tool_input.command.
target_files() {
  local root="$1" f cwd
  cwd=$(jq -r '.cwd // empty' <<<"$PAYLOAD"); cwd=${cwd:-$root}
  {
    jq -r '.tool_input.file_path // .tool_input.notebook_path // .tool_response.filePath // empty' <<<"$PAYLOAD"
    jq -r 'if (.tool_input.command|type)=="string" then .tool_input.command else empty end' <<<"$PAYLOAD" \
      | sed -nE 's/^\*\*\* (Add|Update|Delete) File: (.+)$/\2/p; s/^\*\*\* Move to: (.+)$/\1/p'
  } | while IFS= read -r f; do
    [ -z "$f" ] && continue
    case "$f" in /*) ;; *) f="$cwd/$f" ;; esac
    f=$(realpath -m "$f")
    printf '%s\n' "${f#"$root"/}"
  done | sort -u
}
