<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpaController extends AbstractController
{
    #[Route('/spa/{vueRouting}', name: 'spa_app', requirements: ['vueRouting' => '.*'], defaults: ['vueRouting' => ''])]// ignorer les routes de l'api et du build
    public function index(): Response
    {
        return $this->render('spa/index.html.twig', [
            
        ]);
    }
}
