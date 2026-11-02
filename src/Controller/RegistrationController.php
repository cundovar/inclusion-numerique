<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\AuthAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription libre d'un apprenant (compte facultatif : les cours publics restent ouverts sans compte).
 * Le compte sert à retrouver les cours et exercices personnalisés que le médiateur lui réserve.
 */
final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, Security $security, UserRepositoryInterface $userRepository): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('spa_app', ['vueRouting' => 'mon-espace']);
        }

        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setRoles([]);
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $userRepository->save($user);

            $security->login($user, AuthAuthenticator::class, 'main');

            return new RedirectResponse($this->generateUrl('spa_app', ['vueRouting' => 'mon-espace']));
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}
