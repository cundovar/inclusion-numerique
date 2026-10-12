<?php

declare(strict_types=1);

use App\Application\QcmResult\Command\RecordQcmAttemptCommand;
use App\Application\QcmResult\Handler\RecordQcmAttemptHandler;
use App\Domain\QCM\Repository\QCMRepositoryInterface;
use App\Domain\QcmResult\Exception\InvalidQcmAttemptException;
use App\Domain\QcmResult\QcmResultsGatewayInterface;
use App\Entity\ChoicesQCM;
use App\Entity\LanguageQCM;
use App\Entity\NiveauQCM;
use App\Entity\QCM;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class RecordQcmAttemptHandlerTest extends TestCase
{
    /** @var array<int, QCM> */
    private array $questions = [];

    protected function setUp(): void
    {
        // Question 1 (CSS / basique) : choix 11 correct, 12 faux. Question 2 : choix 21 faux, 22 correct.
        // Question 3 appartient à un autre langage.
        $this->questions = [
            1 => $this->makeQuestion(1, 'CSS', 'basique', [11 => true, 12 => false]),
            2 => $this->makeQuestion(2, 'CSS', 'basique', [21 => false, 22 => true]),
            3 => $this->makeQuestion(3, 'PHP', 'basique', [31 => true]),
        ];
    }

    public function testCorrectsAnswersFromDatabaseAndForwardsThem(): void
    {
        $gateway = $this->createMock(QcmResultsGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('recordAttempt')
            ->with(7, 'css', 'Basique', [
                ['questionId' => 1, 'choiceId' => 11, 'correct' => true],
                ['questionId' => 2, 'choiceId' => 21, 'correct' => false],
            ])
            ->willReturn(['id' => 'abc', 'score' => 1, 'total' => 2]);

        // Langage et niveau comparés sans tenir compte de la casse
        $result = $this->handler($gateway)->handle(new RecordQcmAttemptCommand($this->makeUser(7), 'css', 'Basique', [1 => 11, 2 => 21]));

        self::assertSame(['id' => 'abc', 'score' => 1, 'total' => 2], $result);
    }

    /**
     * @dataProvider invalidAnswers
     *
     * @param array<int, int> $answers
     */
    public function testRejectsInvalidAttemptWithoutCallingTheService(array $answers, string $message): void
    {
        $gateway = $this->createMock(QcmResultsGatewayInterface::class);
        $gateway->expects($this->never())->method('recordAttempt');

        $this->expectException(InvalidQcmAttemptException::class);
        $this->expectExceptionMessage($message);

        $this->handler($gateway)->handle(new RecordQcmAttemptCommand($this->makeUser(7), 'CSS', 'basique', $answers));
    }

    /**
     * @return iterable<string, array{array<int, int>, string}>
     */
    public static function invalidAnswers(): iterable
    {
        yield 'aucune réponse' => [[], 'Aucune réponse'];
        yield 'question inconnue' => [[99 => 1], 'Question 99 inconnue'];
        yield 'question d\'un autre QCM' => [[3 => 31], 'La question 3 n\'appartient pas au QCM CSS / basique'];
        yield 'choix d\'une autre question' => [[1 => 21], 'Le choix 21 n\'appartient pas à la question 1'];
    }

    private function handler(QcmResultsGatewayInterface $gateway): RecordQcmAttemptHandler
    {
        $repository = $this->createMock(QCMRepositoryInterface::class);
        $repository->method('findById')->willReturnCallback(fn (int $id): ?QCM => $this->questions[$id] ?? null);

        return new RecordQcmAttemptHandler($repository, $gateway);
    }

    /**
     * @param array<int, bool> $choices identifiant du choix => bonne réponse
     */
    private function makeQuestion(int $id, string $language, string $level, array $choices): QCM
    {
        $question = new QCM();
        $this->setEntityId($question, $id);
        $question->setLanguageQCM((new LanguageQCM())->setName($language));
        $question->setNiveauQCM((new NiveauQCM())->setTitre($level));

        foreach ($choices as $choiceId => $isCorrect) {
            $choice = (new ChoicesQCM())->setQuestion('choix '.$choiceId)->setIsCorrect($isCorrect);
            $this->setEntityId($choice, $choiceId);
            $question->addChoicesQCM($choice);
        }

        return $question;
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $this->setEntityId($user, $id);

        return $user;
    }

    private function setEntityId(object $entity, int $id): void
    {
        // Entités Doctrine sans setter d'id : on simule un objet déjà persisté
        (new \ReflectionProperty($entity, 'id'))->setValue($entity, $id);
    }
}
