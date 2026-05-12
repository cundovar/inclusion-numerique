<?php

namespace App\Controller\Api;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Domain\UserCustomization\Repository\UserCustomizationRepositoryInterface;
use App\Service\CustomizationValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/customization')]
class CustomizationController extends AbstractController
{
    public function __construct(
        private UserCustomizationRepositoryInterface $customizationRepository,
        private SiteConfigurationRepositoryInterface $siteConfigRepository,
        private CustomizationValidator $validator
    ) {}

    /**
     * Récupérer la configuration par défaut du site (public)
     */
    #[Route('/defaults', name: 'api_customization_defaults', methods: ['GET'])]
    public function getDefaults(): JsonResponse
    {
        $settings = $this->siteConfigRepository->getDefaultSettings();

        return $this->json([
            'settings' => $settings
        ]);
    }

    /**
     * Récupérer la personnalisation de l'utilisateur (merge avec defaults)
     */
    #[Route('', name: 'api_customization_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function get(): JsonResponse
    {
        $user = $this->getUser();
        $customization = $this->customizationRepository->findOrCreateForUser($user);

        // Merger avec les defaults du site
        $defaults = $this->siteConfigRepository->getDefaultSettings();
        $userSettings = $customization->getSettings();
        $mergedSettings = array_replace_recursive($defaults, $userSettings);

        return $this->json([
            'settings' => $mergedSettings,
            'updatedAt' => $customization->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Sauvegarder la personnalisation
     */
    #[Route('', name: 'api_customization_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function save(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['settings'])) {
            return $this->json(['error' => 'Settings requis'], Response::HTTP_BAD_REQUEST);
        }

        // Valider les paramètres
        $validatedSettings = $this->validator->validate($data['settings']);

        if (empty($validatedSettings)) {
            return $this->json(['error' => 'Aucun paramètre valide fourni'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer ou créer la personnalisation
        $customization = $this->customizationRepository->findOrCreateForUser($user);

        // Merger les nouveaux paramètres avec les existants
        $currentSettings = $customization->getSettings();
        $mergedSettings = array_replace_recursive($currentSettings, $validatedSettings);

        $customization->setSettings($mergedSettings);

        $this->customizationRepository->save($customization);

        return $this->json([
            'message' => 'Personnalisation sauvegardée',
            'settings' => $customization->getSettings(),
            'updatedAt' => $customization->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Réinitialiser la personnalisation aux valeurs par défaut
     */
    #[Route('/reset', name: 'api_customization_reset', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reset(): JsonResponse
    {
        $user = $this->getUser();
        $customization = $this->customizationRepository->findOrCreateForUser($user);

        $customization->resetToDefaults();

        $this->customizationRepository->save($customization);

        return $this->json([
            'message' => 'Personnalisation réinitialisée',
            'settings' => $customization->getSettings(),
            'updatedAt' => $customization->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupérer les classes Tailwind autorisées
     */
    #[Route('/allowed-classes', name: 'api_customization_allowed_classes', methods: ['GET'])]
    public function getAllowedClasses(): JsonResponse
    {
        return $this->json([
            'classes' => $this->validator->getAllowedClassesByCategory()
        ]);
    }
}
