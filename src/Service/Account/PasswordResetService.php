<?php

declare(strict_types=1);

namespace App\Service\Account;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Mot de passe oublié : un lien à usage unique, valable une heure, envoyé à l'adresse du compte.
 * La réponse est la même que l'adresse existe ou non, et une seule demande est acceptée toutes les 5 minutes par compte.
 */
final class PasswordResetService
{
    public const TOKEN_TTL = '+1 hour';
    public const COOLDOWN = '-5 minutes';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepositoryInterface $userRepository,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%env(MAILER_FROM)%')] private string $from,
        #[Autowire('%app.site_name%')] private string $siteName = 'Inclusion numérique',
    ) {}

    /**
     * Envoie le lien si un compte a cette adresse ; ne dit jamais si c'est le cas.
     *
     * @param \Closure(string): string $linkFor adresse absolue de la page de réinitialisation pour un jeton
     */
    public function request(string $email, \Closure $linkFor): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null || $user->getEmail() === null) {
            return;
        }

        $tokens = $this->entityManager->getRepository(PasswordResetToken::class);
        $recent = $tokens->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :user AND t.createdAt > :since')
            ->setParameter('user', $user)
            ->setParameter('since', new \DateTimeImmutable(self::COOLDOWN))
            ->getQuery()
            ->getSingleScalarResult();
        if ((int) $recent > 0) {
            return;
        }

        $this->forget($user);
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->entityManager->persist(new PasswordResetToken($user, self::hash($token), new \DateTimeImmutable(self::TOKEN_TTL)));
        $this->entityManager->flush();

        $this->mailer->send((new TemplatedEmail())
            ->from(new Address($this->from, $this->siteName))
            ->to(new Address($user->getEmail(), (string) $user->getUsername()))
            ->subject('Choisir un nouveau mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->textTemplate('emails/reset_password.txt.twig')
            ->context(['pseudo' => $user->getUsername(), 'lien' => $linkFor($token)]));
    }

    /**
     * Compte concerné par un jeton encore valable, sinon null.
     */
    public function userFor(string $token): ?User
    {
        $reset = $this->entityManager->getRepository(PasswordResetToken::class)->findOneBy(['tokenHash' => self::hash($token)]);

        return $reset === null || $reset->isExpired() ? null : $reset->getUser();
    }

    /**
     * Change le mot de passe et invalide tous les liens de ce compte. Renvoie false si le jeton ne vaut plus.
     */
    public function reset(string $token, string $plainPassword): bool
    {
        $user = $this->userFor($token);
        if ($user === null) {
            return false;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->forget($user);
        $this->entityManager->flush();

        return true;
    }

    private function forget(User $user): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(PasswordResetToken::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
