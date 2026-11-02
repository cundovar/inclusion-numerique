<?php

namespace App\Controller;

use App\Service\LegalInformation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages légales : mentions légales, politique de confidentialité et conditions d'utilisation.
 * Les informations de l'éditeur viennent du back-office (Configuration du site → Informations légales).
 */
class TermsController extends AbstractController
{
    public function __construct(private LegalInformation $legalInformation) {}

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('terms/terms.html.twig', ['legal' => $this->legalInformation->all()]);
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('terms/privacy.html.twig', ['legal' => $this->legalInformation->all()]);
    }

    #[Route('/mentions-legales', name: 'app_legal')]
    public function legal(): Response
    {
        return $this->render('terms/legal.html.twig', ['legal' => $this->legalInformation->all()]);
    }
}
