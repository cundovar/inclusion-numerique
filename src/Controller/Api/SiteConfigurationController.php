<?php

namespace App\Controller\Api;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Apparence globale du site (nom, couleurs de l'en-tête et des menus), en lecture publique.
 * Elle se modifie dans le back-office ; il n'existe pas de personnalisation par visiteur.
 */
#[Route('/api/site-configuration')]
class SiteConfigurationController extends AbstractController
{
    public function __construct(
        private SiteConfigurationRepositoryInterface $siteConfigRepository,
    ) {}

    #[Route('', name: 'api_site_configuration', methods: ['GET'])]
    public function get(): JsonResponse
    {
        return $this->json([
            'settings' => $this->siteConfigRepository->getDefaultSettings(),
        ]);
    }
}
