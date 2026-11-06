<?php

declare(strict_types=1);

namespace App\Service\Capture;

use App\Entity\CourseMedia;
use App\Service\CaptureFileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Capture automatique d'une page publique (étape 4 du plan, règles CAP-01 à CAP-07).
 * Le connecteur choisit le site ; le moteur refuse les destinations privées et les comportements non sûrs.
 */
final class AutomaticCaptureService
{
    public const FORMATS = ['ordinateur', 'mobile'];
    private const MAX_ELEMENTS = 9;
    private const DEFAULT_REVIEW_DELAY = '+3 months';

    public function __construct(
        private CaptureRunnerInterface $runner,
        private CaptureFileManager $files,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
    ) {}

    public function isCapturableUrl(string $url): bool
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https'
            && isset($parts['host'])
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }

    /**
     * @param array{url?: mixed, format?: mixed, elements?: mixed, service?: mixed, legende?: mixed, texteAlternatif?: mixed, aRevoirLe?: mixed} $input
     * @return array{capture: CourseMedia, elements: list<array{repere: int, texte: string}>, titrePage: string}
     *
     * @throws CaptureFailed refus du moteur ou capture indisponible
     * @throws InvalidCaptureRequest données invalides
     */
    public function capture(array $input, string $author): array
    {
        $errors = [];
        $url = trim((string) ($input['url'] ?? ''));
        if (!$this->isCapturableUrl($url)) {
            throw new CaptureFailed('ADRESSE_REFUSEE', 'Utilisez une adresse https publique, sans identifiants dans l’URL.');
        }
        $format = (string) ($input['format'] ?? 'ordinateur');
        if (!\in_array($format, self::FORMATS, true)) {
            $errors['format'] = 'Format attendu : ordinateur ou mobile.';
        }
        $elements = $this->elements($input['elements'] ?? [], $errors);
        $nextReview = new \DateTimeImmutable(self::DEFAULT_REVIEW_DELAY);
        if (($input['aRevoirLe'] ?? null) !== null && $input['aRevoirLe'] !== '') {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $input['aRevoirLe']);
            $date === false ? $errors['aRevoirLe'] = 'Date attendue au format AAAA-MM-JJ.' : $nextReview = $date;
        }
        if ($errors !== []) {
            throw new InvalidCaptureRequest($errors);
        }

        $workDir = $this->projectDir.'/var/capture/'.bin2hex(random_bytes(8));
        $filesystem = new Filesystem();
        try {
            $result = $this->runner->run([
                'url' => $url,
                'elements' => $elements,
                'format' => $format,
                'outDir' => $workDir,
            ]);
            // Le moteur vérifie aussi les redirections et bloque les adresses réseau privées.
            if (!$this->isCapturableUrl((string) $result['urlFinale'])) {
                throw new CaptureFailed('REDIRECTION_REFUSEE', 'La redirection ne mène pas vers une adresse https publique.');
            }

            $capture = (new CourseMedia())
                ->setKind(CourseMedia::KIND_CAPTURE)
                ->setService($this->nullable($input['service'] ?? null))
                ->setSourceUrl((string) $result['urlFinale'])
                ->setCapturedAt(new \DateTimeImmutable('today'))
                ->setNextReviewAt($nextReview)
                ->setCaption((string) ($input['legende'] ?? ''))
                ->setAltText((string) ($input['texteAlternatif'] ?? ''))
                ->setAuthor($author);

            $source = (string) $result['fichier'];
            if (!str_starts_with((string) realpath($source), (string) realpath($workDir)) || !is_file($source)) {
                throw new CaptureFailed('ERREUR_TECHNIQUE', 'Fichier de capture introuvable.');
            }
            $filename = hash_file('sha256', $source).'.jpg';
            $capture->setFilename($filename);

            $violations = $this->validator->validate($capture);
            if (\count($violations) > 0) {
                $fieldErrors = [];
                foreach ($violations as $violation) {
                    $fieldErrors[$violation->getPropertyPath()] = (string) $violation->getMessage();
                }
                throw new InvalidCaptureRequest($fieldErrors);
            }

            $target = $this->files->path($filename);
            $filesystem->mkdir(\dirname($target));
            $filesystem->rename($source, $target, true);
            $this->files->describe($capture);
            $this->entityManager->persist($capture);
            $this->entityManager->flush();

            return ['capture' => $capture, 'elements' => $result['elements'], 'titrePage' => (string) $result['titre']];
        } finally {
            $filesystem->remove($workDir);
        }
    }

    /**
     * @param array<string, string> $errors
     * @return list<array{repere: int, texte?: string, selecteur?: string}>
     */
    private function elements(mixed $raw, array &$errors): array
    {
        if (!\is_array($raw) || \count($raw) > self::MAX_ELEMENTS) {
            $errors['elements'] = sprintf('Liste de %d éléments au plus attendue.', self::MAX_ELEMENTS);

            return [];
        }
        $elements = [];
        $seen = [];
        foreach (array_values($raw) as $index => $element) {
            $marker = \is_array($element) ? (int) ($element['repere'] ?? 0) : 0;
            $text = \is_array($element) ? trim((string) ($element['texte'] ?? '')) : '';
            $selector = \is_array($element) ? trim((string) ($element['selecteur'] ?? '')) : '';
            if ($marker < 1 || $marker > self::MAX_ELEMENTS || isset($seen[$marker])) {
                $errors["elements[$index].repere"] = 'Repère attendu : un numéro de 1 à 9, différent pour chaque élément.';
            }
            if (($text === '') === ($selector === '') || mb_strlen($text) > 120 || mb_strlen($selector) > 200) {
                $errors["elements[$index]"] = 'Indiquez soit le texte visible de l\'élément, soit un sélecteur CSS.';
            }
            $seen[$marker] = true;
            $elements[] = ['repere' => $marker] + ($text !== '' ? ['texte' => $text] : ['selecteur' => $selector]);
        }

        return $elements;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
