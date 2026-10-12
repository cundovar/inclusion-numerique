# Rapport VAE CDA — Synthèse actuelle

> Document canonique du dossier VAE. Les faits doivent être confirmés dans le code, les tests, la CI ou l'historique Git avant publication.

## Projet

- **Projet :** plateforme pédagogique Symfony/Vue.js hybride.
- **Backend :** Symfony 6.4, Doctrine ORM, API Platform, MySQL.
- **Frontend :** Vue 3, Pinia, Vite, PWA.
- **Infrastructure :** Docker Compose, Nginx, GitHub Actions.
- **Architecture :** MVC Symfony avec migration partielle vers DDD/CQRS/hexagonal.

## Bloc 1 — Développer une application sécurisée

### Preuves principales

- Composants Vue réutilisables et gestion d'état Pinia.
- Services métier et handlers séparés des contrôleurs.
- Sanitisation HTML côté backend et frontend.
- Authentification, rôles, validation serveur et accès API.

### Points à vérifier ou améliorer

- Dépendances vulnérables relevées par l'audit CI (1 alerte critique `twig/twig`, 18 élevées côté Composer ; 8 élevées côté npm) : faille critique Twig corrigée (Twig 3.30.0), autres mises à jour à faire ; audits non bloquants à ce jour.

- Gestion des secrets dans les fichiers `.env`.
- Rate limiting réellement configuré sur les endpoints sensibles.
- Headers CSP, HSTS et protections HTTP dans l'environnement actif.

## Bloc 2 — Concevoir et développer une application en couches

### Preuves principales

- Domaine `Note` organisé avec interfaces, commandes, handlers et infrastructure Doctrine.
- Repositories et services injectés par Symfony.
- Migrations Doctrine versionnées.
- Séparation observable entre HTTP, application, domaine et infrastructure pour le périmètre Note.

### Limite actuelle

L'ancien dossier `src/Repository/` a été supprimé (commit `954d9e45`) : les repositories sont désormais déclarés par contexte dans `src/Domain/` (interfaces) et `src/Infrastructure/` (Doctrine). La couche Application (commands/queries/handlers) ne couvre encore que AgentCourse, Favorite et Note ; le périmètre exact est à confirmer avec `docs/final-hexagonal-audit.md`.

## Bloc 3 — Préparer le déploiement

### Preuves principales

- Docker et Docker Compose.
- Workflow GitHub Actions dans `.github/workflows/ci.yml`.
- Tests PHPUnit, analyse PHPStan et build frontend.
- Audit Composer/npm dans la CI.
- Manifest et service worker PWA.

### Limites actuelles

- La présence d'un workflow ne prouve pas un déploiement réussi.
- La couverture réelle et les résultats CI doivent être documentés à partir d'exécutions vérifiables.
- Boucle locale `make check` ajoutée ; au 2026-10-12, elle passe de bout en bout (PHPStan sans erreur, 23 tests PHPUnit sur `main`, build Vite) après correction d'un setter Doctrine supprimé par erreur, couverte par un test de régression (voir [preuves.md](preuves.md)). Confirmé par la CI ([run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068)).
- Déploiement en production prouvé par la CI ([run `38231755068`](https://github.com/cundovar/vue-symfony/actions/runs/38231755068), `c29ce7b5`), mais aucun test fonctionnel post-déploiement : le fonctionnement en production n'est pas vérifié.

## Positionnement du dossier

Le registre [preuves.md](preuves.md) est la source de vérité pour les fichiers, tests, commits et statuts. Ce rapport ne doit contenir que des synthèses renvoyant à ce registre.
