# Permissions

Chaque commande lancée par un agent passe par une décision : **autorisée** sans question, **à confirmer** par l'utilisateur, ou **interdite**. Claude Code et Codex ont chacun leur fichier, avec les mêmes décisions.

| Décision | Commandes | Pourquoi |
|---|---|---|
| **Autorisée** | `make check`, `check-fast`, `lint-php`, `lint-front`, `analyse`, `test`, `test-front`, `test-qcm-results`, `test-e2e`, `build-front`, `docs-build`, `docs-dev` ; `make up`, `dev`, `start`, `stop`, `logs` ; `docker compose ps`, `logs`, `exec` | La boucle de validation doit tourner sans friction |
| **À confirmer** | `git commit`, `git push`, `gh pr` ; `make migrate`, `down`, `restart` ; `rm` | Publie, modifie la base ou arrête l'environnement |
| **Interdite** | `make down-volumes`, `docker compose down -v` ; `sudo rm` | Supprime le volume MySQL ou des fichiers en root : à lancer à la main |

Côté Claude Code seulement : lecture de `git status`, `diff`, `log`, `show`, `ls`, `grep`, `find`, `cat` autorisée ; **lecture interdite** de `.env.local`, `.env.prod`, `.env.local.php`, des sauvegardes `backup*.sql`, `backups/` et `mysql_data/`.

## Où c'est écrit

::: code-group

```json [.claude/settings.json]
{
  "permissions": {
    "allow": ["Bash(make check)", "Bash(make docs-build)", "…"],
    "ask":   ["Bash(git commit *)", "Bash(git push *)", "Bash(make migrate)", "…"],
    "deny":  ["Read(./.env.local)", "Bash(make down-volumes)", "Bash(docker compose down -v*)", "…"]
  }
}
```

```python [.codex/rules/project.rules]
prefix_rule(
    pattern = ["make", "down-volumes"],
    decision = "forbidden",
    justification = "Supprime le volume MySQL. À lancer manuellement par l'utilisateur.",
    match = ["make down-volumes"],
)
```

:::

Chaque règle Codex porte un exemple (`match`) que Codex vérifie au chargement.

## Vérifié

Le 2026-10-13, avec `codex execpolicy check --rules .codex/rules/project.rules` :

| Commande | Décision |
|---|---|
| `make check` | `allow` |
| `git push origin main` | `prompt` |
| `make down-volumes` | `forbidden` |
| `docker compose down -v` | `forbidden` |

## Limites

::: warning
- Une permission porte sur le texte de la commande : `docker compose exec php …` est autorisé quelle que soit la commande exécutée dans le conteneur.
- `.claude/settings.local.json` (personnel, non versionné) peut ajouter des autorisations par-dessus.
- Codex n'a pas d'équivalent des interdictions de **lecture** de fichiers : seul le hook bloque leur **modification**.
:::
