<?php

declare(strict_types=1);

namespace App\Service\Learner;

use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Jetons QCM signés, valables quinze minutes, sans score ni tentative enregistrés. */
final class LearnerQcmSessionService
{
    public function __construct(private EntityManagerInterface $entityManager, #[Autowire('%kernel.secret%')] private string $secret) {}

    /** @return array<string, mixed>|null */
    public function start(User $learner, int $id): ?array
    {
        $qcm = $this->accessible($learner, $id);
        if ($qcm === null) {
            return null;
        }
        $choices = array_map(static fn ($choice) => ['id' => $choice->getId(), 'texte' => strip_tags((string) $choice->getQuestion())], $qcm->getChoicesQCMs()->toArray());

        return ['session' => $this->token(['qcm' => $id, 'user' => $learner->getId(), 'exp' => time() + 900]), 'question' => strip_tags((string) $qcm->getTitre()), 'choix' => $choices];
    }

    /** @return array<string, mixed>|null */
    public function submit(User $learner, string $session, int $choiceId): ?array
    {
        $data = $this->verify($session);
        if ($data === null || (int) $data['user'] !== $learner->getId()) {
            return null;
        }
        $qcm = $this->accessible($learner, (int) $data['qcm']);
        if ($qcm === null) {
            return null;
        }
        foreach ($qcm->getChoicesQCMs() as $choice) {
            if ($choice->getId() === $choiceId) {
                return ['correct' => $choice->getIsCorrect(), 'explication' => strip_tags((string) $choice->getExplication()), 'solution' => strip_tags((string) $qcm->getSolution())];
            }
        }

        return null;
    }

    /**
     * QCM accessibles à la personne (publics ou qui lui sont réservés), par thème et niveau, pour en choisir un à démarrer.
     *
     * @return list<array<string, mixed>>
     */
    public function list(User $learner, ?int $themeId = null): array
    {
        $criteria = $themeId === null ? [] : ['languageQCM' => $themeId];
        $qcms = array_filter(
            $this->entityManager->getRepository(QCM::class)->findBy($criteria, ['id' => 'ASC'], 200),
            static fn (QCM $qcm) => $qcm->isAccessibleTo($learner),
        );

        return array_values(array_map(static fn (QCM $qcm) => [
            'id' => $qcm->getId(),
            'question' => strip_tags((string) $qcm->getTitre()),
            'theme' => $qcm->getLanguageQCM()?->getName(),
            'themeId' => $qcm->getLanguageQCM()?->getId(),
            'niveau' => $qcm->getNiveauQCM()?->getTitre(),
        ], $qcms));
    }

    private function accessible(User $learner, int $id): ?QCM
    {
        $qcm = $this->entityManager->find(QCM::class, $id);

        return $qcm instanceof QCM && $qcm->isAccessibleTo($learner) ? $qcm : null;
    }

    /** @param array<string, int|null> $data */
    private function token(array $data): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode($data, \JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payload, $this->secret, true);

        return $payload.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    /** @return array<string, int>|null */
    private function verify(string $token): ?array
    {
        [$payload, $signature] = array_pad(explode('.', $token, 2), 2, '');
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, $this->secret, true)), '+/', '-_'), '=');
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/'), true) ?: '', true);

        return hash_equals($expected, $signature) && is_array($data) && isset($data['qcm'], $data['user'], $data['exp']) && (int) $data['exp'] >= time() ? $data : null;
    }
}
