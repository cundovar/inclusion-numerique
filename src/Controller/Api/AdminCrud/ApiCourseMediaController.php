<?php

declare(strict_types=1);

namespace App\Controller\Api\AdminCrud;

use App\Entity\AgentCourseGeneration;
use App\Entity\CourseMedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api/admin/course-media')]
final class ApiCourseMediaController extends AbstractController
{
    private const MAX_SIZE = 8_000_000;
    private const MIMES = ['image/png', 'image/jpeg', 'image/webp'];

    public function __construct(private EntityManagerInterface $em, private ParameterBagInterface $parameters) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = max(1, min(100, $request->query->getInt('limit', 25)));
        $builder = $this->em->getRepository(CourseMedia::class)->createQueryBuilder('media')
            ->orderBy('media.id', 'DESC')
            ->setMaxResults($limit);
        if ($request->query->has('generationId')) $builder->andWhere('IDENTITY(media.generation) = :generationId')->setParameter('generationId', $request->query->getInt('generationId'));
        if ($request->query->has('courseId')) $builder->andWhere('IDENTITY(media.course) = :courseId')->setParameter('courseId', $request->query->getInt('courseId'));
        if ($request->query->has('checksum')) $builder->andWhere('media.checksum = :checksum')->setParameter('checksum', trim((string) $request->query->get('checksum')));
        if ($request->query->has('q')) $builder->andWhere('LOWER(media.altText) LIKE :query OR LOWER(media.caption) LIKE :query OR LOWER(media.prompt) LIKE :query')->setParameter('query', '%' . mb_strtolower(trim((string) $request->query->get('q'))) . '%');
        return new JsonResponse(array_map(fn (CourseMedia $item) => $this->map($item), $builder->getQuery()->getResult()));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $media = $this->em->find(CourseMedia::class, $id);
        return $media ? new JsonResponse($this->map($media)) : new JsonResponse(['error' => 'Média non trouvé'], Response::HTTP_NOT_FOUND);
    }

    #[Route('', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile || !$file->isValid()) return new JsonResponse(['error' => 'Fichier image requis'], Response::HTTP_BAD_REQUEST);
        if ($file->getSize() === false || $file->getSize() > self::MAX_SIZE) return new JsonResponse(['error' => 'Image trop volumineuse'], Response::HTTP_BAD_REQUEST);
        $mime = (string) $file->getMimeType();
        if (!in_array($mime, self::MIMES, true)) return new JsonResponse(['error' => 'Format d’image non autorisé'], Response::HTTP_BAD_REQUEST);
        $dimensions = @getimagesize((string) $file->getPathname());
        if (!$dimensions) return new JsonResponse(['error' => 'Image invalide'], Response::HTTP_BAD_REQUEST);
        $alt = trim((string) $request->request->get('altText', ''));
        if ($alt === '') return new JsonResponse(['error' => 'altText est requis'], Response::HTTP_BAD_REQUEST);

        $extension = match ($mime) { 'image/jpeg' => 'jpg', 'image/webp' => 'webp', default => 'png' };
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = $this->parameters->get('kernel.project_dir') . '/public/uploads/course-media';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) return new JsonResponse(['error' => 'Impossible de préparer le stockage'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $file->move($directory, $filename);
        $media = new CourseMedia($filename, $mime, (int) $dimensions[0], (int) $dimensions[1], hash_file('sha256', $directory . '/' . $filename), $alt);
        $media->setCaption($request->request->get('caption'));
        $media->setPrompt($request->request->get('prompt'));
        if ($request->request->has('generationId')) {
            $generation = $this->em->find(AgentCourseGeneration::class, (int) $request->request->get('generationId'));
            if (!$generation) { @unlink($directory . '/' . $filename); return new JsonResponse(['error' => 'Génération non trouvée'], Response::HTTP_BAD_REQUEST); }
            $media->setGeneration($generation);
        }
        $this->em->persist($media); $this->em->flush();
        return new JsonResponse($this->map($media), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $media = $this->em->find(CourseMedia::class, $id);
        if (!$media) return new JsonResponse(['error' => 'Média non trouvé'], Response::HTTP_NOT_FOUND);
        if ($media->getCourse()) return new JsonResponse(['error' => 'Impossible de supprimer un média associé à un cours'], Response::HTTP_CONFLICT);
        $file = $this->parameters->get('kernel.project_dir') . '/public' . $media->getPublicPath();
        if (is_file($file)) @unlink($file);
        $this->em->remove($media); $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function map(CourseMedia $item): array
    {
        return ['id' => $item->getId(), 'url' => $item->getPublicPath(), 'mimeType' => $item->getMimeType(), 'width' => $item->getWidth(), 'height' => $item->getHeight(), 'checksum' => $item->getChecksum(), 'altText' => $item->getAltText(), 'caption' => $item->getCaption(), 'prompt' => $item->getPrompt(), 'courseId' => $item->getCourse()?->getId(), 'generationId' => $item->getGeneration()?->getId(), 'createdAt' => $item->getCreatedAt()->format(DATE_ATOM)];
    }
}
