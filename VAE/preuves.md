# Registre des preuves VAE

Une preuve doit être reliée à un fichier, une classe, une méthode, un test, un commit ou une exécution identifiable.

| Bloc | Compétence / sujet | Preuve | État | À confirmer |
|---|---|---|---|---|
| 1 | Interface utilisateur | `front/src/components/` et stores Pinia | Présent dans le code | Tests UI ciblés |
| 1 | Sanitisation HTML | `src/Service/HtmlSanitizerService.php`, `front/src/components/ui/SafeHtml.vue` | Présent dans le code | Exécution des tests associés |
| 1 | Authentification et autorisation | `config/packages/security.yaml`, `src/Security/` | Présent dans le code | Parcours HTTP complet |
| 1 | Protection des secrets | `.env*`, configuration CI | Point de vigilance | Rotation et absence de secrets réels |
| 2 | Architecture applicative | `src/Domain/` et `src/Infrastructure/` (repositories par contexte), `src/Application/` (handlers AgentCourse, Favorite, Note) ; ancien `src/Repository/` supprimé (commit `954d9e45`) | Présent dans le code | Couche Application limitée à 3 contextes ; croiser avec `docs/final-hexagonal-audit.md` |
| 2 | CQRS Notes | `src/Application/Note/Handler/` | Présent dans le code | Tests exécutés |
| 2 | Accès aux données | `src/Infrastructure/Note/Repository/` et migrations | Présent dans le code | Vérification des requêtes critiques |
| 3 | CI/CD | `.github/workflows/ci.yml` | Présent dans le dépôt | Dernier run CI réussi |
| 3 | Tests backend | `tests/`, `make test` (`composer test` dans le conteneur `php`) | Vérifié localement — 2026-10-12, `OK (19 tests, 72 assertions)` sur `93a22763` + modifications non commitées | Validation CI datée sur un commit propre |
| 3 | Build frontend | `front/package.json`, `make build-front`, workflow CI | Vérifié localement — 2026-10-12, build Vite réussi sur `93a22763` + modifications non commitées | Validation CI datée sur un commit propre |
| 3 | Analyse statique PHP | `phpstan.dist.neon` (niveau 5 + baseline), `make analyse` | Point de vigilance — 2026-10-12 : 4 erreurs `Menus::setPositionMenus()` inexistante (`ApiMenuController.php`, `PositionMenus.php`) | Correction du bug ; PHPStan informatif en CI |
| 3 | Lint frontend | `front/eslint.config.js`, `make lint-front` | Vérifié localement — 2026-10-12 : 0 erreur, 1698 avertissements | ESLint absent de la CI ; réduire les avertissements |
| 3 | Boucle de validation locale | `Makefile` : `make check-fast` / `make check` (syntaxe PHP, PHPStan, ESLint, PHPUnit, build Vite dans Docker) | Présent dans le code | `make check` vert de bout en bout (bloqué par PHPStan) |
| 3 | PWA | `manifest.webmanifest`, `front/public/spa/sw.js` | Présent dans le dépôt | Test mobile ou Lighthouse |

## Documents techniques rattachés

| Bloc | Document | Utilité VAE |
|---|---|---|
| 2 | [`docs/final-hexagonal-audit.md`](../docs/final-hexagonal-audit.md) | État réel de l'architecture hexagonale |
| 2 | [`docs/hexagonal-audit.md`](../docs/hexagonal-audit.md) | Analyse de l'architecture et de ses limites |
| 2 | [`docs/migration-mcp-vers-symfony.md`](../docs/migration-mcp-vers-symfony.md) | Conception et migration d'un composant technique |
| 2 | [`docs/architecture-hexagonale-option1.md`](../docs/architecture-hexagonale-option1.md) | Proposition d'architecture concrète |
| 2 | [`docs/architecture-hexagonale-option2.md`](../docs/architecture-hexagonale-option2.md) | Variante et comparaison des choix d'architecture |
| 3 | [`docs/CI_CD.md`](../docs/CI_CD.md) | CI, tests, build frontend et déploiement |

## Statuts autorisés

- **Présent dans le code** : fichier ou logique observé, sans preuve d'exécution.
- **Vérifié localement** : commande ou test exécuté avec résultat conservé.
- **Validé par la CI** : run CI identifiable et réussi.
- **Déployé/vérifié en production** : preuve d'environnement et de validation disponible.
- **Point de vigilance** : risque ou lacune identifiée.
