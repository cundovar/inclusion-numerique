<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class TestController extends AbstractController
{
    #[Route('/ping', name: 'api_ping')]
    public function ping(): JsonResponse
    {
        
        // necessaire de boucle tous les produits et ajouter dans data en tableau associatif

        // revoi en tableau simple facile e convertire en json


        return new JsonResponse([

            'message' => 'pong',
            'code'=> "second test"
        
        ]);
    }
#[Route('/test', name: 'api_test')]
    public function test() : JsonResponse
    {

        return new JsonResponse([
            "message" => "je suis l'api"
        ]);
    }
}
