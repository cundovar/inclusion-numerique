<?php

declare(strict_types=1);

namespace App\Service\Rag;

use App\Entity\PageContent;
use App\Entity\RagPassage;
use Doctrine\ORM\EntityManagerInterface;

/** Construit localement les passages recherchables des cours publiés. */
final class RagIndex
{
    private const PASSAGE_LENGTH = 900;
    // Le service vectoriel accepte au plus 100 passages par requête
    private const BATCH_SIZE = 50;

    public function __construct(private EntityManagerInterface $entityManager, private VectorStore $vectors) {}

    /** Synchronise le petit catalogue courant avant une recherche, sans réseau ni modèle externe. */
    public function refresh(): void
    {
        foreach ($this->entityManager->getRepository(PageContent::class)->findAll() as $course) {
            $this->sync($course);
        }
        $this->entityManager->flush();
    }

    /** @return array{indexed:int,failed:list<int>} cours vectorisés, et identifiants des cours en échec */
    public function rebuildVectors(): array
    {
        $indexed = 0;
        $failed = [];
        foreach ($this->entityManager->getRepository(PageContent::class)->findAll() as $course) {
            $published = $course->isPublished() && self::text(($course->getCode() ?: null) ?? $course->getContent()) !== '';
            if (!$this->indexCourse($course)) {
                $failed[] = (int) $course->getId();
            } elseif ($published) {
                ++$indexed;
            }
        }
        return ['indexed' => $indexed, 'failed' => $failed];
    }

    /** Met à jour les points d'un cours : remplacés s'il est publié, retirés sinon. Renvoie false si le service a échoué. */
    public function indexCourse(PageContent $course): bool
    {
        $text = self::text(($course->getCode() ?: null) ?? $course->getContent());
        if (!$course->isPublished() || $text === '') {
            return $this->vectors->deleteCourse((int) $course->getId());
        }

        return $this->indexVectors($course, self::split($text), hash('sha256', $course->getStatus().'\0'.$text));
    }

    private function sync(PageContent $course): void
    {
        $existing = $this->entityManager->getRepository(RagPassage::class)->findBy(['course' => $course], ['position' => 'ASC']);
        // Le texte d'un cours rédigé par le connecteur est dans `code` ; `content` sert aux anciens cours.
        $text = self::text(($course->getCode() ?: null) ?? $course->getContent());
        $checksum = hash('sha256', $course->getStatus().'\0'.$text);
        if ($course->isPublished() && $existing !== [] && $existing[0]->getChecksum() === $checksum) {
            return;
        }
        if (!$course->isPublished() || $text === '') {
            foreach ($existing as $passage) {
                $this->entityManager->remove($passage);
            }
            $this->vectors->deleteCourse((int) $course->getId());
            return;
        }
        $chunks = self::split($text);
        foreach ($chunks as $position => $content) {
            if (isset($existing[$position])) {
                $existing[$position]->replace($content, $checksum);
                continue;
            }
            $this->entityManager->persist(new RagPassage($course, $position + 1, $content, $checksum));
        }
        foreach (array_slice($existing, count($chunks)) as $passage) {
            $this->entityManager->remove($passage);
        }
        $this->indexVectors($course, $chunks, $checksum);
    }

    /** @param list<string> $chunks */
    private function indexVectors(PageContent $course, array $chunks, string $checksum): bool
    {
        $id = (int) $course->getId();
        $passages = array_map(static fn (string $text, int $offset) => ['id' => $id.'-'.($offset + 1), 'text' => $text, 'course_id' => $id, 'position' => $offset + 1, 'checksum' => $checksum], $chunks, array_keys($chunks));
        // Les passages d'une ancienne version plus longue ne doivent pas rester dans l'index
        if (!$this->vectors->deleteCourse($id)) {
            return false;
        }
        foreach (array_chunk($passages, self::BATCH_SIZE) as $batch) {
            if (!$this->vectors->index($batch)) {
                return false;
            }
        }

        return true;
    }

    /** @return list<string> */
    private static function split(string $text): array
    {
        $chunks = [];
        while (mb_strlen($text) > self::PASSAGE_LENGTH) {
            $cut = mb_strrpos(mb_substr($text, 0, self::PASSAGE_LENGTH), ' ');
            $cut = $cut === false ? self::PASSAGE_LENGTH : $cut;
            $chunks[] = trim(mb_substr($text, 0, $cut));
            $text = trim(mb_substr($text, $cut));
        }
        if ($text !== '') {
            $chunks[] = $text;
        }

        return $chunks;
    }

    private static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($html ?? '')) ?? '');
    }
}
