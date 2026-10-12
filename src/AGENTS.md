# Backend — conventions

## Architecture hexagonale
Modèle de référence : le domaine **Note**.
- `Domain/<Contexte>/` : interfaces (`Repository/*RepositoryInterface.php`, `Service/*Interface.php`) et exceptions métier. Aucune dépendance Doctrine/Symfony.
- `Application/<Contexte>/` : `Command/`, `Query/`, `Handler/`, `DTO/`. Les handlers dépendent des interfaces du Domain.
- `Infrastructure/<Contexte>/` : implémentations (`Repository/Doctrine*Repository.php`, services techniques). Transverse : `Infrastructure/Persistence/` (flusher, transactions).
- `Controller/` : mapping HTTP → command/query → réponse. Pas de logique métier.
- `Entity/` : entités Doctrine (attributs PHP 8, API Platform).

Tout nouveau code métier suit ce découpage ; les controllers dépendent des handlers ou des interfaces, pas des repositories Doctrine.

## Tests
- PHPUnit dans `tests/`, miroir de `src/` (ex. `tests/Application/Note/Handler/`).
- Tout handler ajouté ou modifié a son test. `make test`.

## Qualité
- PHPStan niveau 5 (`phpstan.dist.neon`, baseline `phpstan-baseline.neon`) : `make analyse`. Ne pas ajouter d'erreurs à la baseline pour faire passer.

## Sécurité
- HTML stocké : sanitisé via `Service/HtmlSanitizerService.php` (et `EventListener/PageContentSanitizerListener.php`).
- Accès : `config/packages/security.yaml`, `Security/`.
