<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace d'un apprenant connecté : les contenus que le médiateur lui a réservés, et la gestion de son compte.
 * Les modifications n'acceptent que du JSON (une requête d'un autre site ne peut pas l'envoyer sans autorisation CORS)
 * et demandent le mot de passe actuel.
 */
#[Route('/user-api/mon-espace')]
#[IsGranted('ROLE_USER')]
final class MonEspaceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('', name: 'mon_espace', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->learner();

        $courses = array_values(array_filter(
            $this->reservedTo($user, PageContent::class),
            static fn (PageContent $course) => $course->isPublished() && ($course->getCategory()?->isAccessibleTo($user) ?? true),
        ));
        $parcours = array_values(array_filter(
            $this->reservedTo($user, Category::class),
            static fn (Category $category) => $category->isVisible() !== false,
        ));

        return $this->json([
            'pseudo' => $user->getUsername(),
            'email' => $user->getEmail(),
            'cours' => array_map(static fn (PageContent $course) => [
                'id' => $course->getId(),
                'titre' => $course->getTitle(),
                'slug' => ltrim((string) $course->getPage()?->getSlug(), '/'),
                'parcours' => $course->getCategory()?->getName(),
                'duree' => $course->getDuration(),
            ], $courses),
            'parcours' => array_map(static fn (Category $category) => [
                'id' => $category->getId(),
                'nom' => $category->getName(),
                'description' => $category->getDescription(),
                'illustrationUrl' => $category->getIllustrationUrl(),
            ], $parcours),
            'exercices' => array_map(static fn (QCM $qcm) => [
                'id' => $qcm->getId(),
                'question' => strip_tags((string) $qcm->getTitre()),
                'theme' => $qcm->getLanguageQCM()?->getName(),
                'niveau' => $qcm->getNiveauQCM()?->getTitre(),
            ], $this->reservedTo($user, QCM::class)),
        ]);
    }

    #[Route('/mot-de-passe', name: 'mon_espace_mot_de_passe', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $user = $this->learner();
        $payload = $this->payload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }
        if (!$hasher->isPasswordValid($user, (string) ($payload['motDePasseActuel'] ?? ''))) {
            return $this->json(['message' => 'Le mot de passe actuel est incorrect.'], Response::HTTP_FORBIDDEN);
        }
        $new = (string) ($payload['nouveauMotDePasse'] ?? '');
        if (mb_strlen($new) < RegistrationForm::PASSWORD_MIN_LENGTH || \strlen($new) > 4096) {
            return $this->json(['message' => sprintf('Le nouveau mot de passe compte au moins %d caractères.', RegistrationForm::PASSWORD_MIN_LENGTH)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setPassword($hasher->hashPassword($user, $new));
        $this->entityManager->flush();

        return $this->json(['message' => 'Mot de passe modifié.']);
    }

    /**
     * Suppression définitive du compte ; les accès réservés disparaissent avec lui (tables acces_*, ON DELETE CASCADE).
     */
    #[Route('', name: 'mon_espace_supprimer', methods: ['DELETE'])]
    public function delete(Request $request, UserPasswordHasherInterface $hasher, Security $security): JsonResponse
    {
        $user = $this->learner();
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => "Un compte administrateur se supprime depuis le serveur, pas depuis l'espace personnel."], Response::HTTP_FORBIDDEN);
        }
        $payload = $this->payload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }
        if (!$hasher->isPasswordValid($user, (string) ($payload['motDePasse'] ?? ''))) {
            return $this->json(['message' => 'Le mot de passe est incorrect.'], Response::HTTP_FORBIDDEN);
        }

        $security->logout(false);
        $this->entityManager->remove($this->entityManager->find(User::class, $user->getId()) ?? $user);
        $this->entityManager->flush();

        return $this->json(['message' => 'Compte supprimé.']);
    }

    private function learner(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    /**
     * @template T of PageContent|Category|QCM
     * @param class-string<T> $class
     * @return list<T>
     */
    private function reservedTo(User $user, string $class): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from($class, 'c')
            ->where('c.reserve = true AND :user MEMBER OF c.destinataires')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function payload(Request $request): array|JsonResponse
    {
        if ($request->getContentTypeFormat() !== 'json') {
            return $this->json(['message' => 'Requête JSON attendue.'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }
        $payload = json_decode($request->getContent(), true);

        return \is_array($payload) ? $payload : $this->json(['message' => 'Requête JSON invalide.'], Response::HTTP_BAD_REQUEST);
    }
}
