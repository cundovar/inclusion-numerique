<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RedirectionController extends AbstractController
{
    #[Route('/', name: 'app_redirection')]
    public function index(): Response
    {
        return $this->redirectToRoute('spa_app');
    }
}
