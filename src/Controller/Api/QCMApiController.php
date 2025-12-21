<?php

namespace App\Controller\Api;

use App\Entity\QCM;
use App\Repository\QCMRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qcm')]
final class QCMApiController extends AbstractController
{
    public function __construct(
        private QCMRepository $qcmRepository
    ) {}

    #[Route('', name: 'api_qcm_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $qcms = $this->qcmRepository->findAll();
        return $this->formatQCMsResponse($qcms);
    }

    #[Route('/{id}', name: 'api_qcm_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $qcm = $this->qcmRepository->find($id);

        if (!$qcm) {
            return new JsonResponse(['error' => 'QCM non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->formatQCM($qcm), Response::HTTP_OK);
    }

    #[Route('/language/{language}', name: 'api_qcm_by_language', methods: ['GET'])]
    public function getByLanguage(string $language): JsonResponse
    {
        $qcms = $this->qcmRepository->createQueryBuilder('q')
            ->join('q.languageQCM', 'l')
            ->where('l.name = :language')
            ->setParameter('language', $language)
            ->getQuery()
            ->getResult();

        return $this->formatQCMsResponse($qcms);
    }

    #[Route('/difficulty/{difficulty}', name: 'api_qcm_by_difficulty', methods: ['GET'])]
    public function getByDifficulty(string $difficulty): JsonResponse
    {
        $qcms = $this->qcmRepository->createQueryBuilder('q')
            ->join('q.niveauQCM', 'n')
            ->where('n.titre = :difficulty')
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getResult();

        return $this->formatQCMsResponse($qcms);
    }

    #[Route('/language/{language}/difficulty/{difficulty}', name: 'api_qcm_by_language_and_difficulty', methods: ['GET'])]
    public function getByLanguageAndDifficulty(string $language, string $difficulty): JsonResponse
    {
        $qcms = $this->qcmRepository->createQueryBuilder('q')
            ->join('q.languageQCM', 'l')
            ->join('q.niveauQCM', 'n')
            ->where('l.name = :language')
            ->andWhere('n.titre = :difficulty')
            ->setParameter('language', $language)
            ->setParameter('difficulty', $difficulty)
            ->getQuery()
            ->getResult();

        return $this->formatQCMsResponse($qcms);
    }

    private function formatQCMsResponse(array $qcms): JsonResponse
    {
        $formattedQCMs = array_map(fn($qcm) => $this->formatQCM($qcm), $qcms);
        return new JsonResponse($formattedQCMs, Response::HTTP_OK);
    }

    private function formatQCM(QCM $qcm): array
    {
        return [
            'title' => $qcm->getTitre(),
            'language' => $qcm->getLanguageQCM()?->getName(),
            'difficulty' => $qcm->getNiveauQCM()?->getTitre(),
            'solution' => $qcm->getSolution(),
            'choices' => array_map(function ($choice) {
                return [
                    'label' => $choice->getQuestion(),
                    'isCorrect' => $choice->isCorrect(),
                    'explanation' => $choice->getExplication()
                ];
            }, $qcm->getChoicesQCMs()->toArray())
        ];
    }
}
