# Dossier VAE CDA

Cette documentation est le point d'entrée du dossier VAE pour la plateforme pédagogique Symfony/Vue.

## Documents canoniques

- [Rapport CDA](rapport-cda.md) : synthèse actuelle par bloc.
- [Registre des preuves](preuves.md) : preuves vérifiables et état de validation.
- [Journal](journal.md) : évolutions importantes du dossier.

## Références techniques

Les documents détaillés restent dans `docs/` et `documentations/`. Ils servent de sources techniques et ne doivent pas être recopiés intégralement dans le dossier VAE.

- [CI/CD](../docs/CI_CD.md)
- [Architecture hexagonale](../documentations/architecture/archi-hexagonal-symfony/plan-migration-hexa.md)
- [Présentation du projet](../documentations/doc-pour-portfolio.md)
- [Méthode d'analyse VAE](../agents/agent_VAE.md)

## Références VAE par bloc

### Bloc 2 — Conception et architecture

- [Expression des besoins](../docs/conception/besoins.md) : acteurs, user stories, cas d'utilisation, enchaînement des écrans (formalisé a posteriori).
- [Maquettes](../docs/conception/maquettes.md) : maquettes Figma basse fidélité comparées aux écrans réalisés.
- [Audit hexagonal final](../docs/final-hexagonal-audit.md) : état réel de l'architecture hexagonale.
- [Audit hexagonal](../docs/hexagonal-audit.md) : analyse de l'architecture actuelle et de ses limites.
- [Migration MCP vers Symfony](../docs/migration-mcp-vers-symfony.md) : conception, migration et intégration d'un composant technique.
- [Architecture hexagonale — option 1](../docs/architecture-hexagonale-option1.md) : proposition d'architecture concrète.
- [Architecture hexagonale — option 2](../docs/architecture-hexagonale-option2.md) : variante et comparaison des choix d'architecture.

### Bloc 3 — Déploiement et qualité

- [CI/CD](../docs/CI_CD.md) : CI, tests, build frontend et déploiement.
- [Plan de tests](../docs/tests/plan-de-tests.md) : stratégie, cas de test, recette exécutée, résultats et anomalies.

## Document historique

[`result_analyse_VAE.md`](result_analyse_VAE.md) est conservé comme analyse historique. Il ne constitue plus la source canonique : ses métriques et son statut doivent être vérifiés avant réutilisation.
