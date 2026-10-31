<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Category;
use App\Entity\Diaporama;
use App\Entity\Menus;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * L'API publique ne sert que les cours publiés, en liste comme à l'unité.
 * Un contenu personnalisé (cours, parcours ou QCM réservé) n'est servi qu'à ses destinataires ;
 * un cours d'un parcours réservé et les modules de ce parcours suivent la même règle.
 * Un administrateur voit tout, brouillons compris, pour les relire.
 *
 * Un diaporama n'est servi, à tous, que publié et si sa cible l'est : son cours publié et accessible,
 * ou son chapitre dans un parcours accessible et dont tous les cours sont publiés et ouverts à tous
 * (la synthèse d'un chapitre ne doit rien révéler d'un cours réservé ou retiré).
 */
final class PublishedContentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictToPublished($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictReserved($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictDiaporamas($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->orderByRank($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictToPublished($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictReserved($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictDiaporamas($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    /**
     * Cours d'un module dans l'ordre pédagogique : rang croissant, les cours sans rang à la suite, par ancienneté.
     */
    private function orderByRank(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== PageContent::class) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->addSelect(sprintf('CASE WHEN %s.ordre IS NULL THEN 1 ELSE 0 END AS HIDDEN rankIsMissing', $alias))
            ->addOrderBy('rankIsMissing', 'ASC')
            ->addOrderBy(sprintf('%s.ordre', $alias), 'ASC')
            ->addOrderBy(sprintf('%s.id', $alias), 'ASC');
    }

    private function restrictToPublished(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if ($resourceClass !== PageContent::class || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameter = $queryNameGenerator->generateParameterName('status');
        $queryBuilder
            ->andWhere(sprintf('%s.status = :%s', $alias, $parameter))
            ->setParameter($parameter, PageContent::STATUS_PUBLISHED);
    }

    private function restrictReserved(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (!\in_array($resourceClass, [PageContent::class, Category::class, Menus::class, QCM::class], true) || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $user = $this->security->getUser();
        $user = $user instanceof User ? $user : null;
        $userParameter = $queryNameGenerator->generateParameterName('destinataire');

        // Le contenu lui-même : ouvert à tous, ou réservé à la personne connectée
        if ($resourceClass !== Menus::class) {
            $queryBuilder->andWhere($user === null
                ? sprintf('%s.reserve = false', $alias)
                : sprintf('(%1$s.reserve = false OR :%2$s MEMBER OF %1$s.destinataires)', $alias, $userParameter));
        }

        // Son parcours : un cours ou un module d'un parcours réservé n'est servi qu'aux destinataires du parcours
        if ($resourceClass === PageContent::class || $resourceClass === Menus::class) {
            $category = $queryNameGenerator->generateJoinAlias('parcours_reserve');
            $queryBuilder->andWhere(sprintf(
                '%1$s.category IS NULL OR IDENTITY(%1$s.category) NOT IN (SELECT %2$s.id FROM %3$s %2$s WHERE %2$s.reserve = true%4$s)',
                $alias,
                $category,
                Category::class,
                $user === null ? '' : sprintf(' AND :%s NOT MEMBER OF %s.destinataires', $userParameter, $category),
            ));
        }

        if ($user !== null) {
            $queryBuilder->setParameter($userParameter, $user);
        }
    }

    private function restrictDiaporamas(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if ($resourceClass !== Diaporama::class) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $cours = $queryNameGenerator->generateJoinAlias('cours');
        $chapitre = $queryNameGenerator->generateJoinAlias('chapitre');
        $publie = $queryNameGenerator->generateParameterName('publie');
        $coursPublie = $queryNameGenerator->generateParameterName('cours_publie');
        $chapitreComplet = $queryNameGenerator->generateParameterName('chapitre_publie');
        $user = $this->security->getUser();
        $user = $user instanceof User ? $user : null;
        $userParameter = $queryNameGenerator->generateParameterName('destinataire');

        // Parcours réservés à d'autres personnes que celle connectée
        $parcoursReserves = function () use ($queryNameGenerator, $user, $userParameter): string {
            $category = $queryNameGenerator->generateJoinAlias('parcours_reserve');

            return sprintf(
                'SELECT %1$s.id FROM %2$s %1$s WHERE %1$s.reserve = true%3$s',
                $category,
                Category::class,
                $user === null ? '' : sprintf(' AND :%s NOT MEMBER OF %s.destinataires', $userParameter, $category),
            );
        };
        $autreCours = $queryNameGenerator->generateJoinAlias('cours_du_chapitre');

        $queryBuilder
            ->leftJoin(sprintf('%s.cours', $alias), $cours)
            ->leftJoin(sprintf('%s.chapitre', $alias), $chapitre)
            ->andWhere(sprintf('%s.status = :%s', $alias, $publie))
            ->andWhere(sprintf(
                '(%1$s.id IS NOT NULL AND %1$s.status = :%3$s AND (%1$s.reserve = false%4$s) AND (%1$s.category IS NULL OR IDENTITY(%1$s.category) NOT IN (%5$s)))'
                .' OR (%2$s.id IS NOT NULL AND (%2$s.category IS NULL OR IDENTITY(%2$s.category) NOT IN (%6$s))'
                .' AND NOT EXISTS (SELECT %7$s.id FROM %8$s %7$s WHERE %7$s.menu = %2$s AND (%7$s.status <> :%9$s OR %7$s.reserve = true)))',
                $cours,
                $chapitre,
                $coursPublie,
                $user === null ? '' : sprintf(' OR :%s MEMBER OF %s.destinataires', $userParameter, $cours),
                $parcoursReserves(),
                $parcoursReserves(),
                $autreCours,
                PageContent::class,
                $chapitreComplet,
            ))
            ->setParameter($publie, Diaporama::STATUS_PUBLISHED)
            ->setParameter($coursPublie, PageContent::STATUS_PUBLISHED)
            ->setParameter($chapitreComplet, PageContent::STATUS_PUBLISHED);

        if ($user !== null) {
            $queryBuilder->setParameter($userParameter, $user);
        }
    }
}
