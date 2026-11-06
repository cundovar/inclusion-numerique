<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegistrationForm;
use App\Service\Account\PasswordResetService;
use App\Service\OAuth\PublicUrl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Mot de passe oublié. Le jeton reçu par e-mail est aussitôt rangé en session et retiré de l'adresse,
 * pour qu'il n'apparaisse ni dans l'historique ni dans l'en-tête Referer d'un lien suivant.
 */
final class ResetPasswordController extends AbstractController
{
    private const SESSION_KEY = 'reset_password_token';

    public function __construct(private PasswordResetService $resets, private PublicUrl $publicUrl) {}

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('forgot_password', $request->getPayload()->getString('_csrf_token'))) {
                return $this->render('security/forgot_password.html.twig', ['error' => 'La page a expiré : recommencez.', 'sent' => false], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
            $email = trim($request->getPayload()->getString('email'));
            if ($email !== '') {
                $base = $this->publicUrl->base($request);
                $this->resets->request($email, fn (string $token) => $base.$this->generateUrl('app_reset_password', ['token' => $token]));
            }

            // Même réponse que l'adresse corresponde à un compte ou non
            return $this->render('security/forgot_password.html.twig', ['error' => null, 'sent' => true]);
        }

        return $this->render('security/forgot_password.html.twig', ['error' => null, 'sent' => false]);
    }

    #[Route('/mot-de-passe-oublie/nouveau/{token}', name: 'app_reset_password', defaults: ['token' => null], methods: ['GET', 'POST'])]
    public function reset(Request $request, ?string $token): Response
    {
        $session = $request->getSession();
        if ($token !== null) {
            $session->set(self::SESSION_KEY, $token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = (string) $session->get(self::SESSION_KEY, '');
        if ($token === '' || $this->resets->userFor($token) === null) {
            return $this->render('security/reset_password.html.twig', ['state' => 'invalid', 'error' => null], new Response(status: Response::HTTP_GONE));
        }

        if ($request->isMethod('POST')) {
            $password = $request->getPayload()->getString('password');
            $error = match (true) {
                !$this->isCsrfTokenValid('reset_password', $request->getPayload()->getString('_csrf_token')) => 'La page a expiré : recommencez.',
                mb_strlen($password) < RegistrationForm::PASSWORD_MIN_LENGTH || \strlen($password) > 4096 => sprintf('Le mot de passe compte au moins %d caractères.', RegistrationForm::PASSWORD_MIN_LENGTH),
                $password !== $request->getPayload()->getString('password_confirm') => 'Les deux mots de passe ne sont pas identiques.',
                default => null,
            };
            if ($error !== null) {
                return $this->render('security/reset_password.html.twig', ['state' => 'form', 'error' => $error], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }

            $this->resets->reset($token, $password);
            $session->remove(self::SESSION_KEY);

            return $this->render('security/reset_password.html.twig', ['state' => 'done', 'error' => null]);
        }

        return $this->render('security/reset_password.html.twig', ['state' => 'form', 'error' => null]);
    }
}
