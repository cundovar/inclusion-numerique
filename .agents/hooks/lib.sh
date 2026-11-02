#!/usr/bin/env bash
# Fonctions partagées par les hooks Codex et Claude Code.
read_payload() { PAYLOAD=$(cat); }

# Racine git du cwd de la session d'abord : dans un worktree, CLAUDE_PROJECT_DIR désigne le dépôt
# principal, et les chemins du worktree (.claude/worktrees/…) n'y correspondraient à aucune règle.
project_root() {
  local cwd
  cwd=$(jq -r '.cwd // empty' <<<"$PAYLOAD")
  if [ -n "$cwd" ] && git -C "$cwd" rev-parse --show-toplevel 2>/dev/null; then return; fi
  if [ -n "${CLAUDE_PROJECT_DIR:-}" ]; then realpath -m "$CLAUDE_PROJECT_DIR"; return; fi
  cwd=${cwd:-$PWD}
  git -C "$cwd" rev-parse --show-toplevel 2>/dev/null || realpath -m "$cwd"
}

target_files() {
  local root="$1" cwd path absolute
  cwd=$(jq -r '.cwd // empty' <<<"$PAYLOAD")
  cwd=${cwd:-$root}
  {
    jq -r '.tool_input.file_path // .tool_input.notebook_path // empty' <<<"$PAYLOAD"
    jq -r 'if (.tool_input.command | type) == "string" then .tool_input.command else empty end' <<<"$PAYLOAD" |
      sed -nE 's/^\*\*\* (Add|Update|Delete) File: (.+)$/\2/p; s/^\*\*\* Move to: (.+)$/\1/p'
  } | while IFS= read -r path; do
    [ -n "$path" ] || continue
    case "$path" in /*) absolute=$(realpath -m "$path");; *) absolute=$(realpath -m "$cwd/$path");; esac
    case "$absolute" in "$root"/*) printf '%s\n' "${absolute#"$root"/}";; *) printf '%s\n' "@outside:$absolute";; esac
  done | sort -u
}
