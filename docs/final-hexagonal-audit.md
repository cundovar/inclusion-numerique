# Audit final hexagonal

## Résultat

L'application est alignée avec une architecture hexagonale "Doctrine en infrastructure":

- aucun `App\Repository\*` restant dans `src`
- aucun `getRepository(...)` hors `src/Infrastructure`
- aucun `EntityManagerInterface` dans `src/Controller`, `src/Service`, `src/EventListener` ou `src/Command`
- les ports domaine sont injectés dans les adaptateurs d'entrée
- Doctrine est concentré dans `Infrastructure`, les entités ORM et un listener Doctrine ciblé

## Vérifications

Commandes de contrôle utilisées:

```bash
rg -n 'App\\Repository\\|repositoryClass:' src
rg -n 'EntityManagerInterface' src/Controller src/Service src/EventListener src/Command
rg -n 'Doctrine\\ORM\\|EntityManagerInterface|ServiceEntityRepository|ManagerRegistry|getRepository\\(' src
php bin/console lint:container
```

## Fuites structurelles restantes

### 1. Entités ORM

Les classes dans `src/Entity` importent encore `Doctrine\ORM\Mapping as ORM`.

Ce n'est pas une fuite bloquante dans la cible actuelle. C'est normal si la stratégie retenue est:

- domaine applicatif et use cases derrière des ports
- persistence Doctrine isolée dans `Infrastructure`
- entités encore mappées avec Doctrine

Pour aller vers un hexagonal "pur", il faudrait séparer:

- modèles de domaine
- modèles de persistence
- mapping entre les deux

Ce n'est pas nécessaire pour considérer cette base comme hexagonale au sens Symfony pragmatique.

### 2. Listener Doctrine ciblé

[`src/EventListener/PageContentSanitizerListener.php`](/home/cundo/Bureau/perso/symfoy_projets/symfo_vue/devdoc-local/src/EventListener/PageContentSanitizerListener.php) dépend encore de:

- `Doctrine\ORM\Events`
- `PrePersistEventArgs`
- `PreUpdateEventArgs`
- `#[AsEntityListener(...)]`

Ce point reste un couplage technique assumé. Il est acceptable si le sanitizer est considéré comme une politique de persistence.

Si vous voulez aller plus loin:

- déplacer ce comportement vers un service applicatif explicite appelé avant sauvegarde
- ou garder ce listener et l'assumer comme détail `Infrastructure`

## Ce qui est propre

- `src/Application` ne dépend pas de Doctrine
- `src/Application` ne dépend pas de `App\Repository`
- `src/Controller` ne fait plus d'accès repository Doctrine direct
- `src/Service` ne dépend plus de `EntityManagerInterface`
- `src/EventListener/UserPageVisitListener.php` passe par un port
- `src/Security` charge les utilisateurs via `UserRepositoryInterface` et un provider infrastructure dédié
- `src/Repository` est vidé

## Verdict

Oui, l'application est désormais cohérente avec une architecture hexagonale pragmatique sur Symfony.

Le seul couplage Doctrine encore visible hors `Infrastructure` est:

- le mapping ORM dans `src/Entity`
- un listener Doctrine spécialisé sur `PageContent`

Si votre définition de "100% hexagonal" est:

- pas de repository legacy
- pas de `EntityManagerInterface` hors infrastructure
- pas de `getRepository(...)` hors infrastructure
- ports explicites pour les accès persistence

alors l'objectif est atteint.

Si votre définition est plus stricte et exclut aussi Doctrine des entités, alors il reste une seconde phase d'architecture beaucoup plus lourde.
