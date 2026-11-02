<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Le site n'a pas d'inscription : cette commande est le seul moyen d'ouvrir le back-office.
 */
final class CreateAdminCommandTest extends KernelTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->tester = new CommandTester((new Application($kernel))->find('app:create-admin'));
    }

    public function testCreatesAnAdminWithAHashedPassword(): void
    {
        $this->tester->setInputs(['mot-de-passe-solide']);

        self::assertSame(Command::SUCCESS, $this->tester->execute(['username' => 'redaction']));

        $user = static::getContainer()->get(UserRepositoryInterface::class)->findByUsername('redaction');
        self::assertNotNull($user);
        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertNotSame('mot-de-passe-solide', $user->getPassword());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'mot-de-passe-solide'));
    }

    public function testUpdatesTheExistingAccountInsteadOfDuplicatingIt(): void
    {
        $this->tester->setInputs(['premier-mot-de-passe']);
        $this->tester->execute(['username' => 'redaction']);
        $this->tester->setInputs(['second-mot-de-passe']);

        self::assertSame(Command::SUCCESS, $this->tester->execute(['username' => 'redaction']));
        self::assertSame(1, static::getContainer()->get(UserRepositoryInterface::class)->countAll());
    }

    public function testRejectsAShortPassword(): void
    {
        $this->tester->setInputs(['court']);

        self::assertSame(Command::INVALID, $this->tester->execute(['username' => 'redaction']));
        self::assertNull(static::getContainer()->get(UserRepositoryInterface::class)->findByUsername('redaction'));
    }
}
