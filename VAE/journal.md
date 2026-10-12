# Journal documentaire VAE

## 2026-09-28

- Regroupement de la documentation VAE autour de `README.md`, `rapport-cda.md` et `preuves.md`.
- Conservation de `result_analyse_VAE.md` comme historique, avec retrait de son statut de source canonique.
- Identification de la CI GitHub Actions comme preuve présente dans le dépôt, sans conclure à un déploiement réussi.
- Mise à jour du registre avec les limites à vérifier : secrets, rate limiting, headers HTTP, résultats de tests et validation de production.
- Rattachement au dossier VAE des références CI/CD, audits hexagonaux, migration MCP et options d'architecture.

## 2026-10-12

- Ajout d'une boucle de validation locale unique dans le `Makefile` (`make check-fast`, `make check`) et d'un harness pour agents IA partagé entre Claude Code et Codex : consignes `AGENTS.md` (racine, `src/`, `front/`, importées par les `CLAUDE.md`), permissions `.claude/settings.json` et règles `.codex/rules/project.rules`, hooks communs `.agents/hooks/` (protection de fichiers, lint après modification, rappel `make check`).
- Premiers résultats d'exécution datés dans le registre : PHPUnit 19 tests OK, build Vite OK, ESLint 0 erreur ; PHPStan en échec (4 erreurs réelles sur `Menus::setPositionMenus()`).
- Correction d'une affirmation obsolète : l'ancien `src/Repository/` n'existe plus (supprimé au commit `954d9e45`).
- `make down` ne supprime plus les volumes Docker ; l'ancien comportement est isolé dans `make down-volumes`.
