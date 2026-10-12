# Registre des preuves VAE

Une preuve doit être reliée à un fichier, une classe, une méthode, un test, un commit ou une exécution identifiable.

| Bloc | Compétence / sujet | Preuve | État | À confirmer |
|---|---|---|---|---|
| 1 | Interface utilisateur | `front/src/components/` et stores Pinia | Présent dans le code | Tests UI ciblés |
| 1 | Sanitisation HTML | `src/Service/HtmlSanitizerService.php`, `front/src/components/ui/SafeHtml.vue` | Présent dans le code | Exécution des tests associés |
| 1 | Authentification et autorisation | `config/packages/security.yaml`, `src/Security/` | Présent dans le code | Parcours HTTP complet |
| 1 | Dépendances vulnérables | Audits `composer audit` et `npm audit` du workflow CI | Point de vigilance — CI 2026-10-12 ([run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068), `c29ce7b5`) : Composer 50 alertes sur 14 paquets (1 critique `twig/twig` injection de code via `{% use %}`, 18 élevées), npm 12 vulnérabilités (8 élevées, 4 modérées) ; audits informatifs, non bloquants. Correction 2026-10-12 (vérifiée localement, branche `fix/twig-security-update`) : `twig/twig` 3.22.2 → 3.30.0, `composer audit` sans alerte critique ni alerte Twig, 34 alertes restantes (14 élevées) | Validation CI de la correction ; mettre à jour `league/commonmark`, composants Symfony, EasyAdmin ; rendre l'audit bloquant |
| 1 | Protection des secrets | `.env*`, configuration CI | Point de vigilance | Rotation et absence de secrets réels |
| 2 | Architecture applicative | `src/Domain/` et `src/Infrastructure/` (repositories par contexte), `src/Application/` (handlers AgentCourse, Favorite, Note) ; ancien `src/Repository/` supprimé (commit `954d9e45`) | Présent dans le code | Couche Application limitée à 3 contextes ; croiser avec `docs/final-hexagonal-audit.md` |
| 2 | CQRS Notes | `src/Application/Note/Handler/` | Présent dans le code | Tests exécutés |
| 2 | Accès aux données | `src/Infrastructure/Note/Repository/` et migrations | Présent dans le code | Vérification des requêtes critiques |
| 3 | CI/CD et déploiement | `.github/workflows/ci.yml` (jobs dépendances, qualité PHP/front, tests, audit, couverture, build, rapport, déploiement OVH par rsync) | Déployé — [run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068) sur `c29ce7b5` (push `main`, 2026-10-12 09:43–09:46 UTC) : tous les jobs réussis, dont « Deploy production » (`cache:clear` prod `[OK]`) | Fonctionnement en production non vérifié (aucun test fonctionnel post-déploiement) |
| 3 | Tests backend | `tests/`, `make test` (`composer test` dans le conteneur `php`) | Validé par la CI — [run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068) sur `c29ce7b5` : PHPUnit `OK (23 tests, 86 assertions)` (dont `tests/Entity/PositionMenusTest.php`, test de régression vérifié en échec sans le correctif) | Couverture : rapport Clover produit en artefact CI, taux non documenté |
| 3 | Build frontend | `front/package.json`, `make build-front`, workflow CI | Validé par la CI — [run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068) sur `c29ce7b5` : jobs « Frontend quality » et « Build release » réussis | — |
| 3 | Analyse statique PHP | `phpstan.dist.neon` (niveau 5 + baseline), `make analyse` | Validé par la CI — [run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068) sur `c29ce7b5` : étape PHPStan réussie (job encore informatif), après restauration de `Menus::setPositionMenus()` (commit `c111d030`, supprimé par erreur au commit `2e9227b1`) | Rendre PHPStan bloquant en CI (retirer `continue-on-error`) |
| 3 | Lint frontend | `front/eslint.config.js`, `make lint-front` | Vérifié localement — 2026-10-12 : 0 erreur, 1698 avertissements | ESLint absent de la CI ; réduire les avertissements |
| 3 | Boucle de validation locale | `Makefile` : `make check-fast` / `make check` (syntaxe PHP, PHPStan, ESLint, PHPUnit, build Vite dans Docker) | Vérifié localement — 2026-10-12 : `make check` réussi de bout en bout sur `3110284d` + modifications non commitées | Validation CI datée sur un commit propre |
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
