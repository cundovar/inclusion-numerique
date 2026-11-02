<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Menus;
use App\Entity\PageContent;
use App\Service\CourseVisibility;
use App\Service\Pdf\CoursePdfRenderer;
use App\Service\Pdf\PdfDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Téléchargement en PDF d'un cours ou d'un module (fiche d'atelier), avec ses captures.
 * Mêmes règles que l'API publique : un contenu non publié ou réservé à d'autres répond « introuvable ».
 */
final class CoursePdfController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseVisibility $visibility,
        private CoursePdfRenderer $renderer,
    ) {}

    #[Route('/cours/{slug}.pdf', name: 'cours_pdf', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function course(string $slug, Request $request): Response
    {
        $course = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(PageContent::class, 'c')
            ->join('c.page', 'p')
            ->where('p.slug = :slug')
            ->setParameter('slug', '/'.$slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$course instanceof PageContent || !$this->visibility->canSee($course)) {
            throw new NotFoundHttpException('Cours introuvable.');
        }

        return $this->send($this->renderer->course($course, $request->getSchemeAndHttpHost()), $this->visibility->isPublic($course), $request);
    }

    #[Route('/module/{id}.pdf', name: 'module_pdf', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function module(int $id, Request $request): Response
    {
        $module = $this->entityManager->find(Menus::class, $id);
        $courses = $module === null ? [] : array_values(array_filter(
            $this->entityManager->getRepository(PageContent::class)->findBy(['menu' => $module]),
            fn (PageContent $course) => $this->visibility->canSee($course),
        ));
        if ($module === null || $courses === []) {
            throw new NotFoundHttpException('Module introuvable.');
        }

        $public = array_reduce($courses, fn (bool $all, PageContent $course) => $all && $this->visibility->isPublic($course), true);

        return $this->send($this->renderer->module($module, CoursePdfRenderer::sortForModule($courses), $request->getSchemeAndHttpHost()), $public, $request);
    }

    private function send(PdfDocument $document, bool $public, Request $request): Response
    {
        $response = new BinaryFileResponse($document->path, Response::HTTP_OK, ['Content-Type' => 'application/pdf'], false);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $document->filename);
        $response->setEtag($document->fingerprint);
        // Jamais dans un cache partagé (la session est lue pour vérifier l'accès). Le navigateur garde le PDF
        // d'un cours public mais redemande à chaque fois s'il a changé (réponse 304 sinon) ; il ne garde pas
        // celui d'un contenu réservé ou d'un brouillon
        $response->setPrivate();
        $response->headers->addCacheControlDirective($public ? 'no-cache' : 'no-store');
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->isNotModified($request);

        return $response;
    }
}
