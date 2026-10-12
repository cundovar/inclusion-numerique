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
- Correction de `Menus::setPositionMenus()` (supprimé par erreur lors de la réparation d'un conflit au commit `2e9227b1`) avec test de régression `tests/Entity/PositionMenusTest.php` vérifié en échec sans le correctif ; `make check` réussi de bout en bout pour la première fois (PHPStan 0 erreur ; 23 tests sur `main`).
- Preuves tests, build, PHPStan et déploiement passées en « Validé par la CI » / « Déployé » sur la base du [run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068) (`c29ce7b5`) ; ajout du point de vigilance « dépendances vulnérables » issu des audits Composer et npm de ce run.
- Correction de la faille critique Twig (« PHP code injection via `{% use %}` ») : `twig/twig` 3.22.2 → 3.30.0 et extensions Twig 3.29.0 ; `make check`, `lint:twig` et pages rendues par Twig vérifiés localement.
- Correction Twig fusionnée (PR #37) et validée par la CI ([run `38236154178`](https://github.com/cundovar/vue-symfony/actions/runs/38236154178), `97f17bac`) : plus d'alerte critique Composer, 34 alertes restantes dont 14 élevées.
- Ajout de la couverture du référentiel CDA RNCP37873 dans `preuves.md` : 11 compétences relevées sur la fiche France Compétences, chacune rattachée à ses preuves avec un niveau de couverture. 4 compétences couvertes, 6 partielles, 1 lacune (analyse des besoins et maquettage).
