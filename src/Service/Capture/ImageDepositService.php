<?php

declare(strict_types=1);

namespace App\Service\Capture;

use App\Entity\CourseMedia;
use App\Service\CaptureFileManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Dépôt d'une image de l'ordinateur par un agent, en deux temps : le connecteur décrit l'image et obtient
 * une adresse à usage unique, puis le fichier y est envoyé (par exemple avec curl depuis Claude Code).
 * L'image est enregistrée comme une capture du back-office : mêmes formats, même fiche obligatoire.
 */
final class ImageDepositService
{
    public const TTL_SECONDS = 600;
    private const DEFAULT_REVIEW_DELAY = '+3 months';
    private const KEY_PREFIX = 'depot_image_';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private CaptureFileManager $files,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {}

    /**
     * Vérifie la fiche de l'image et réserve un jeton de dépôt.
     *
     * @param array<string, mixed> $input
     * @return array{jeton: string, expireLe: \DateTimeImmutable}
     *
     * @throws InvalidCaptureRequest fiche incomplète ou invalide
     */
    public function prepare(array $input, string $author): array
    {
        $errors = [];
        $sheet = [
            'type' => (string) ($input['type'] ?? ''),
            'legende' => (string) ($input['legende'] ?? ''),
            'texteAlternatif' => (string) ($input['texteAlternatif'] ?? ''),
            'service' => $this->nullable($input['service'] ?? null),
            'pageSource' => $this->nullable($input['pageSource'] ?? null),
            'dateCapture' => $this->date($input['dateCapture'] ?? null, 'dateCapture', $errors)?->format('Y-m-d'),
            'aRevoirLe' => ($this->date($input['aRevoirLe'] ?? null, 'aRevoirLe', $errors) ?? new \DateTimeImmutable(self::DEFAULT_REVIEW_DELAY))->format('Y-m-d'),
            'auteur' => $author,
        ];
        if (!\in_array($sheet['type'], CourseMedia::KINDS, true)) {
            $errors['type'] = 'Type attendu : capture (page officielle) ou illustration (image fictive ou pédagogique).';
        }
        if ($errors !== []) {
            throw new InvalidCaptureRequest($errors);
        }

        // Le fichier n'est pas encore là : on valide la fiche seule
        $this->validate($this->media($sheet)->setFilename('en-attente'));

        $token = bin2hex(random_bytes(24));
        $expiresAt = new \DateTimeImmutable('+'.self::TTL_SECONDS.' seconds');
        $item = $this->cache->getItem(self::KEY_PREFIX.$token)->set($sheet)->expiresAt($expiresAt);
        $this->cache->save($item);

        return ['jeton' => $token, 'expireLe' => $expiresAt];
    }

    /**
     * Enregistre le fichier envoyé pour ce jeton ; le jeton ne sert qu'une fois.
     *
     * @throws CaptureFailed jeton inconnu ou expiré, image déjà déposée
     * @throws InvalidCaptureRequest fichier refusé
     */
    public function deposit(string $token, ?UploadedFile $file): CourseMedia
    {
        $key = self::KEY_PREFIX.(preg_match('/^[a-f0-9]{48}$/', $token) === 1 ? $token : 'invalide');
        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            throw new CaptureFailed('JETON_INVALIDE', 'Adresse de dépôt inconnue, expirée ou déjà utilisée : demandez-en une nouvelle avec preparer_depot_image.');
        }

        if ($file === null) {
            throw new InvalidCaptureRequest(['image' => 'Envoyez le fichier dans le champ « image » (curl -F image=@fichier.png …).']);
        }
        $violations = $this->validator->validate($file, new Image(
            maxSize: CaptureFileManager::MAX_SIZE,
            mimeTypes: CaptureFileManager::MIME_TYPES,
            mimeTypesMessage: 'Formats acceptés : PNG, JPEG ou WebP.',
        ));
        if (\count($violations) > 0) {
            throw new InvalidCaptureRequest(['image' => (string) $violations->get(0)->getMessage()]);
        }

        // Même nommage que le back-office : le nom vient du contenu
        $filename = sha1_file($file->getPathname()).'.'.($file->guessExtension() ?? 'png');
        $existing = $this->entityManager->getRepository(CourseMedia::class)->findOneBy(['filename' => $filename]);
        if ($existing !== null) {
            throw new CaptureFailed('IMAGE_DEJA_DEPOSEE', sprintf('Cette image est déjà enregistrée : utilisez %s.', $existing->getMarker()), ['marqueur' => $existing->getMarker()]);
        }

        /** @var array<string, ?string> $sheet */
        $sheet = $item->get();
        $media = $this->media($sheet)->setFilename($filename);
        $this->validate($media);

        $target = $this->files->path($filename);
        $file->move(\dirname($target), basename($target));
        $this->files->describe($media);
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        $this->cache->deleteItem($key);

        return $media;
    }

    /**
     * @param array<string, ?string> $sheet
     */
    private function media(array $sheet): CourseMedia
    {
        return (new CourseMedia())
            ->setKind((string) $sheet['type'])
            ->setService($sheet['service'])
            ->setSourceUrl($sheet['pageSource'])
            ->setCapturedAt($sheet['dateCapture'] === null ? null : new \DateTimeImmutable($sheet['dateCapture']))
            ->setNextReviewAt(new \DateTimeImmutable((string) $sheet['aRevoirLe']))
            ->setCaption($sheet['legende'])
            ->setAltText($sheet['texteAlternatif'])
            ->setAuthor($sheet['auteur']);
    }

    private function validate(CourseMedia $media): void
    {
        $errors = [];
        foreach ($this->validator->validate($media) as $violation) {
            $errors[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }
        if ($errors !== []) {
            throw new InvalidCaptureRequest($errors);
        }
    }

    /**
     * @param array<string, string> $errors
     */
    private function date(mixed $value, string $field, array &$errors): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        if ($date === false || $date->format('Y-m-d') !== (string) $value) {
            $errors[$field] = 'Date attendue au format AAAA-MM-JJ.';

            return null;
        }

        return $date;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
