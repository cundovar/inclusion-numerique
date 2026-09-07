<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserApiController extends AbstractController
{
    #[Route('/user-api/me', name: 'me')]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
    // dd(method_exists($user,'getId'));

        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
           
            'id' => $user instanceof \App\Entity\User ? $user->getId() : null,
        ]);
    }
}
