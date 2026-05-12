# Audit architecture hexagonale

## Fait

- `Note` n'utilise plus `App\Repository\NoteRepository`.
- `Favorite` n'utilise plus `App\Repository\FavoriteRepository`.
- `Page` n'utilise plus `App\Repository\PageRepository`.
- Les handlers applicatifs `Note` et `Favorite` dépendent maintenant d'un port `App\Domain\Page\Repository\PageRepositoryInterface`.
- Les implémentations Doctrine actives pour ces modules sont:
  - `App\Infrastructure\Note\Repository\DoctrineNoteRepository`
  - `App\Infrastructure\Favorite\Repository\DoctrineFavoriteRepository`
  - `App\Infrastructure\Page\Repository\DoctrinePageRepository`

## Reste à migrer pour du 100% hexagonal

- `src/Application/*` ne doit dépendre que de ports du domaine ou d'objets applicatifs.
- Les contrôleurs HTTP doivent idéalement parler aux handlers applicatifs, pas aux repositories Doctrine.
- Les services transverses encore couplés à Doctrine doivent être déplacés derrière des ports si on veut une séparation stricte.

## Zones encore couplées à `App\Repository`

- `src/Controller/Api/AdminCrud/*`
- `src/Controller/Api/CustomizationController.php`
- `src/Controller/Api/QCMApiController.php`
- `src/Controller/Api/UserPageVisitController.php`
- `src/Controller/Admin/*`
- `src/Controller/PageVisitController.php`
- `src/Controller/PropositionIAController.php`
- `src/Controller/SitemapController.php`
- `src/Command/CleanupPageVisitsCommand.php`
- `src/Service/SeoService.php`

## Étapes recommandées

1. Migrer par bounded context: `Page`, `Category`, `UserPageVisit`, `SEO`, `QCM`.
2. Définir un port de lecture/écriture par contexte métier au lieu d'exposer les repositories Doctrine directement.
3. Déplacer les requêtes complexes Doctrine dans `Infrastructure/.../Repository`.
4. Garder `Controller`, `Command` et `EventListener` comme adaptateurs d'entrée.
