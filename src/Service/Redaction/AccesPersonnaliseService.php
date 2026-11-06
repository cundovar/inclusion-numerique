<?php

declare(strict_types=1);

namespace App\Service\Redaction;

use App\Entity\Category;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Contenus personnalisés : l'assistant réserve un cours, un parcours ou un QCM à certains apprenants.
 * Un contenu réservé n'est servi qu'à ses destinataires (voir PublishedContentExtension) ; retirer le dernier
 * destinataire ne le rend pas public : il faut l'ouvrir à tous explicitement.
 * Seuls l'identifiant et le pseudo d'un apprenant sortent du site.
 */
final class AccesPersonnaliseService
{
    public const TYPES = ['cours' => PageContent::class, 'parcours' => Category::class, 'qcm' => QCM::class];

    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listLearners(): array
    {
        $learners = array_values(array_filter(
            $this->entityManager->getRepository(User::class)->findBy([], ['username' => 'ASC']),
            static fn (User $user) => !\in_array('ROLE_ADMIN', $user->getRoles(), true),
        ));

        return array_map(fn (User $user) => ['id' => $user->getId(), 'pseudo' => $user->getUsername()] + $this->countsFor($user), $learners);
    }

    /**
     * Contenus réservés, avec leurs destinataires ; seulement ceux d'un apprenant si `$learnerId` est donné.
     *
     * @return list<array<string, mixed>>
     */
    public function listReserved(?int $learnerId = null): array
    {
        $learner = $learnerId === null ? null : $this->learners([$learnerId])[0];
        $items = [];
        foreach (self::TYPES as $type => $class) {
            /** @var list<PageContent|Category|QCM> $contents */
            $contents = $this->entityManager->getRepository($class)->findBy(['reserve' => true], ['id' => 'ASC']);
            foreach ($contents as $content) {
                if ($learner === null || $content->getDestinataires()->contains($learner)) {
                    $items[] = $this->describe($type, $content);
                }
            }
        }

        return $items;
    }

    /**
     * @param list<int> $learnerIds
     * @return array<string, mixed>
     */
    public function grant(string $type, int $id, array $learnerIds): array
    {
        $learners = $this->learners($learnerIds);
        $content = $this->content($type, $id);
        $content->setReserve(true);
        foreach ($learners as $learner) {
            $content->addDestinataire($learner);
        }
        $this->entityManager->flush();

        return $this->describe($type, $content);
    }

    /**
     * @param list<int> $learnerIds
     * @return array<string, mixed>
     */
    public function revoke(string $type, int $id, array $learnerIds): array
    {
        $learners = $this->learners($learnerIds);
        $content = $this->content($type, $id);
        foreach ($learners as $learner) {
            $content->removeDestinataire($learner);
        }
        $this->entityManager->flush();

        return $this->describe($type, $content);
    }

    /**
     * @return array<string, mixed>
     */
    public function openToAll(string $type, int $id): array
    {
        $content = $this->content($type, $id);
        $content->setReserve(false);
        foreach ($content->getDestinataires()->toArray() as $learner) {
            $content->removeDestinataire($learner);
        }
        $this->entityManager->flush();

        return $this->describe($type, $content);
    }

    /**
     * Vérifie les identifiants avant toute écriture : un seul identifiant inconnu et rien n'est enregistré.
     *
     * @param list<int> $learnerIds
     * @return list<User>
     */
    public function learners(array $learnerIds): array
    {
        $learners = [];
        $unknown = [];
        foreach (array_unique($learnerIds) as $id) {
            $user = $this->entityManager->find(User::class, $id);
            if ($user === null) {
                $unknown[] = $id;
            } else {
                $learners[] = $user;
            }
        }
        if ($unknown !== []) {
            throw RedactionError::invalid([['champ' => 'apprenantIds', 'message' => 'Apprenants introuvables (voir lister_apprenants) : '.implode(', ', $unknown).'.']]);
        }

        return $learners;
    }

    private function content(string $type, int $id): PageContent|Category|QCM
    {
        $class = self::TYPES[$type] ?? throw RedactionError::invalid([['champ' => 'type', 'message' => 'Type attendu : cours, parcours ou qcm.']]);
        /** @var PageContent|Category|QCM|null $content */
        $content = $this->entityManager->find($class, $id);

        return $content ?? throw new RedactionError(ucfirst($type).' introuvable.', RedactionError::NOT_FOUND);
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(string $type, PageContent|Category|QCM $content): array
    {
        $title = match (true) {
            $content instanceof PageContent => $content->getTitle(),
            $content instanceof Category => $content->getName(),
            $content instanceof QCM => strip_tags((string) $content->getTitre()),
        };
        $learners = $content->getDestinataires()->map(static fn (User $user) => ['id' => $user->getId(), 'pseudo' => $user->getUsername()])->getValues();

        return [
            'type' => $type,
            'id' => $content->getId(),
            'titre' => $title,
            'reserve' => $content->isReserve(),
            'destinataires' => $learners,
            'visiblePour' => match (true) {
                !$content->isReserve() => 'tout le monde',
                $learners === [] => 'personne (réservé sans destinataire)',
                default => 'ses destinataires seulement',
            },
        ];
    }

    /**
     * @return array{contenusReserves: array<string, int>}
     */
    private function countsFor(User $user): array
    {
        $counts = [];
        foreach (self::TYPES as $type => $class) {
            $counts[$type] = (int) $this->entityManager->createQueryBuilder()
                ->select('COUNT(c.id)')
                ->from($class, 'c')
                ->where('c.reserve = true AND :user MEMBER OF c.destinataires')
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult();
        }

        return ['contenusReserves' => $counts];
    }
}
