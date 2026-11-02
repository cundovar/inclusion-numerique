# mediateur-numerique-formation

Site de formation à la médiation numérique, construit à partir de DevDoc.

La première version vise deux parcours publics, pour adultes et seniors ainsi que pour médiateurs, sans compte apprenant. Le socle Vue 3 / Symfony 6.4 de DevDoc a été repris et allégé des fonctions écartées. L'accueil à deux entrées, les statuts éditoriaux, la gestion des captures et l'assistant de rédaction (connecteur MCP pour claude.ai et ChatGPT, relecture par un autre connecteur, capture automatique des pages officielles autorisées) sont en place.

- [Plan du produit](PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md)
- [Reprise du code de DevDoc](docs/conception/reprise-devdoc.md)
- [Consignes communes pour Codex et Claude Code](AGENTS.md)
- [Démarrage, commandes et contrôles](docs/harness/index.md)
- [Guide d'édition du back-office](docs/fonctionnalites/edition.md)
- [Référentiel de la médiation numérique](regles/README.md)
- [Connecteur MCP de l'assistant de rédaction (claude.ai, ChatGPT)](docs/fonctionnalites/connecteur.md)
- [Site de documentation (VitePress)](docs/index.md) : `make docs-dev` puis <http://localhost:5176/docs/>
- [Dossier VAE (TP CDA, RNCP37873)](VAE/README.md)

Démarrage rapide avec Docker : `cp docs/env.example .env`, puis `make up && make composer && make npm-install && make migrate`. Créer ensuite le compte du back-office avec `php bin/console app:create-admin <nom>` dans le conteneur `php`.
