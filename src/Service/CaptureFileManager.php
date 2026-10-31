<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CourseMedia;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Fichiers image des captures, rangés dans public/uploads/course-media.
 */
final class CaptureFileManager
{
    public const UPLOAD_DIR = 'public'.CourseMedia::PUBLIC_DIR;
    // Fichiers acceptés au dépôt, dans le back-office comme par un agent
    public const MAX_SIZE = '5M';
    public const MIME_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
    ) {}

    public function path(string $filename): string
    {
        return $this->projectDir.'/'.self::UPLOAD_DIR.'/'.basename($filename);
    }

    /**
     * Relit le fichier déposé : type, dimensions et empreinte servent à l'affichage et au suivi des remplacements.
     */
    public function describe(CourseMedia $media): void
    {
        $filename = $media->getFilename();
        if ($filename === null || !is_file($this->path($filename))) {
            return;
        }

        $path = $this->path($filename);
        $size = getimagesize($path);
        if ($size === false) {
            throw new \InvalidArgumentException(sprintf('Le fichier « %s » n\'est pas une image lisible.', $filename));
        }

        $media->describeFile($size['mime'], $size[0], $size[1], (string) hash_file('sha256', $path));
    }

    /**
     * Supprime un fichier remplacé ou devenu inutile.
     */
    public function remove(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = $this->path($filename);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
