<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Category;
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
 */
final class PublishedContentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictToPublished($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictReserved($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->orderByRank($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->restrictToPublished($queryBuilder, $queryNameGenerator, $resourceClass);
        $this->restrictReserved($queryBuilder, $queryNameGenerator, $resourceClass);
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
}
