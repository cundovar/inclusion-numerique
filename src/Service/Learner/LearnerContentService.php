<?php

declare(strict_types=1);

namespace App\Service\Learner;

use App\Entity\Category;
use App\Entity\PageContent;
use App\Entity\RagPassage;
use App\Entity\User;
use App\Service\Rag\RagIndex;
use App\Service\Rag\VectorStore;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Vue de lecture dédiée au connecteur apprenant. Elle ne réutilise pas l'API publique : chaque
 * résultat est filtré avec l'apprenant OAuth avant sa sérialisation, donc n'expose ni brouillon ni
 * contenu attribué à quelqu'un d'autre.
 */
final class LearnerContentService
{
    public function __construct(private EntityManagerInterface $entityManager, private RagIndex $ragIndex, private VectorStore $vectors) {}

    /** @return list<array<string, mixed>> */
    public function parcours(User $learner): array
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['visible' => true], ['ordre' => 'ASC', 'name' => 'ASC']);
        $result = [];
        foreach ($categories as $category) {
            if (!$category->isAccessibleTo($learner)) {
                continue;
            }
            $result[] = [
                'id' => $category->getId(),
                'titre' => $category->getName(),
                'description' => self::text($category->getDescription()),
            ];
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    public function rechercher(User $learner, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $vectorResults = $this->vectors->search($query);
        if ($vectorResults !== null) {
            return $this->vectorResults($learner, $vectorResults);
        }
        $this->ragIndex->refresh();
        $terms = self::booleanTerms($query);
        if ($terms === '') {
            return [];
        }
        $ids = $this->passageIds($terms);
        $result = [];
        foreach ($ids as $id) {
            $passage = $this->entityManager->getRepository(RagPassage::class)->find((int) $id);
            if (!$passage instanceof RagPassage || !$this->canRead($passage->getCourse(), $learner)) {
                continue;
            }
            $page = $passage->getCourse();
            $result[] = $this->summary($page) + [
                'citation' => [
                    'coursId' => $page->getId(),
                    'passage' => $passage->getPosition(),
                    'texte' => $passage->getContent(),
                    'adresse' => self::address($page),
                ],
            ];
            if (count($result) === 10) {
                break;
            }
        }

        return $result;
    }

    /** @param list<array{course_id:int,position:int,text:string,score:float}> $passages @return list<array<string,mixed>> */
    private function vectorResults(User $learner, array $passages): array
    {
        $result = [];
        foreach ($passages as $passage) {
            $page = $this->entityManager->find(PageContent::class, (int) $passage['course_id']);
            if (!$page instanceof PageContent || !$this->canRead($page, $learner)) continue;
            $result[] = $this->summary($page) + ['citation' => ['coursId' => $page->getId(), 'passage' => (int) $passage['position'], 'texte' => (string) $passage['text'], 'adresse' => $page->getPage()?->getSlug() === null ? null : '/spa/'.$page->getPage()->getSlug()]];
            if (count($result) === 10) break;
        }
        return $result;
    }

    /** @return array<string, mixed>|null */
    public function course(User $learner, int $id): ?array
    {
        $page = $this->entityManager->getRepository(PageContent::class)->find($id);
        if (!$page instanceof PageContent || !$this->canRead($page, $learner)) {
            return null;
        }

        return $this->summary($page) + ['contenu' => self::text(self::body($page))];
    }

    private function canRead(PageContent $page, User $learner): bool
    {
        return $page->isPublished()
            && $page->isAccessibleTo($learner)
            && ($page->getCategory() === null || $page->getCategory()->isAccessibleTo($learner));
    }

    /** @return array<string, mixed> */
    private function summary(PageContent $page): array
    {
        return [
            'id' => $page->getId(),
            'titre' => $page->getTitle(),
            'parcours' => $page->getCategory()?->getName(),
            'module' => $page->getMenu()?->getLabel(),
            'duree' => $page->getDuration(),
            'extrait' => mb_substr(self::text(self::body($page)), 0, 500),
            'sourceOfficielle' => $page->getSourceUrl(),
            'adresse' => self::address($page),
        ];
    }

    /** Le texte d'un cours rédigé par le connecteur est dans `code` ; `content` sert aux anciens cours. */
    private static function body(PageContent $page): ?string
    {
        return ($page->getCode() ?: null) ?? $page->getContent();
    }

    private static function address(PageContent $page): ?string
    {
        $slug = $page->getPage()?->getSlug();

        return $slug === null ? null : '/spa/'.ltrim($slug, '/');
    }

    private static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($html ?? '')) ?? '');
    }

    /**
     * Passages qui contiennent tous les mots : plein texte MySQL en production,
     * simple LIKE sur les autres bases (SQLite des tests), qui n'ont pas MATCH … AGAINST.
     *
     * @return list<int|string>
     */
    private function passageIds(string $terms): array
    {
        $connection = $this->entityManager->getConnection();
        if ($connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return $connection->fetchFirstColumn(
                'SELECT id FROM rag_passage WHERE MATCH(content) AGAINST (:terms IN BOOLEAN MODE) ORDER BY MATCH(content) AGAINST (:terms IN BOOLEAN MODE) DESC LIMIT 30',
                ['terms' => $terms],
            );
        }

        $conditions = [];
        $parameters = [];
        foreach (explode(' ', $terms) as $index => $term) {
            $conditions[] = 'LOWER(content) LIKE :t'.$index;
            $parameters['t'.$index] = '%'.trim($term, '+*').'%';
        }

        return $connection->fetchFirstColumn('SELECT id FROM rag_passage WHERE '.implode(' AND ', $conditions).' LIMIT 30', $parameters);
    }

    private static function booleanTerms(string $query): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query), -1, \PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_values(array_unique(array_filter($words, static fn (string $word) => mb_strlen($word) >= 3)));

        return implode(' ', array_map(static fn (string $word) => '+'.$word.'*', array_slice($words, 0, 8)));
    }
}
