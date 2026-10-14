<?php

declare(strict_types=1);

namespace App\Application\QcmResult\Handler;

use App\Application\QcmResult\Command\RecordQcmAttemptCommand;
use App\Domain\QCM\Repository\QCMRepositoryInterface;
use App\Domain\QcmResult\Exception\InvalidQcmAttemptException;
use App\Domain\QcmResult\QcmResultsGatewayInterface;

/**
 * Enregistre une tentative de QCM. La correction est faite ici, à partir des questions en base (MySQL) :
 * le navigateur n'envoie que les choix, jamais le score.
 */
final class RecordQcmAttemptHandler
{
    public function __construct(
        private QCMRepositoryInterface $qcmRepository,
        private QcmResultsGatewayInterface $gateway
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(RecordQcmAttemptCommand $command): array
    {
        if ([] === $command->answers) {
            throw InvalidQcmAttemptException::empty();
        }

        $answers = [];
        foreach ($command->answers as $questionId => $choiceId) {
            $question = $this->qcmRepository->findById($questionId);
            if (null === $question) {
                throw InvalidQcmAttemptException::unknownQuestion($questionId);
            }

            if (0 !== strcasecmp((string) $question->getLanguageQCM()?->getName(), $command->language)
                || 0 !== strcasecmp((string) $question->getNiveauQCM()?->getTitre(), $command->level)) {
                throw InvalidQcmAttemptException::questionOutOfScope($questionId, $command->language, $command->level);
            }

            $choice = null;
            foreach ($question->getChoicesQCMs() as $candidate) {
                if ($candidate->getId() === $choiceId) {
                    $choice = $candidate;
                    break;
                }
            }
            if (null === $choice) {
                throw InvalidQcmAttemptException::unknownChoice($choiceId, $questionId);
            }

            $answers[] = ['questionId' => $questionId, 'choiceId' => $choiceId, 'correct' => true === $choice->getIsCorrect()];
        }

        return $this->gateway->recordAttempt(
            (int) $command->user->getId(),
            $command->language,
            $command->level,
            $answers
        );
    }
}
