<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Entity\OAuthClient;
use App\Entity\OAuthGrant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ConnectorsCommandTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CommandTester $command;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
        $this->command = new CommandTester((new Application(self::$kernel))->find('app:connecteurs'));
    }

    public function testListsConnectorsAndRevokesTheAccessOfOne(): void
    {
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $claude = new OAuthClient('claude-0123456789abcdef', 'Claude', ['https://claude.ai/cb']);
        $chatgpt = new OAuthClient('chatgpt-0123456789abcdef', 'ChatGPT', ['https://chatgpt.com/cb']);
        $grants = [];
        foreach ([$claude, $chatgpt] as $client) {
            $grant = new OAuthGrant($client, $admin, null);
            $grant->rotate(hash('sha256', 'a'.$client->getClientId()), new \DateTimeImmutable('+1 hour'), hash('sha256', 'r'.$client->getClientId()), new \DateTimeImmutable('+1 day'));
            $grants[] = $grant;
            $this->entityManager->persist($client);
            $this->entityManager->persist($grant);
        }
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $this->command->execute([]);
        self::assertStringContainsString('connecteur:Claude#claude-0', $this->command->getDisplay());
        self::assertStringContainsString('redaction', $this->command->getDisplay());

        self::assertSame(Command::FAILURE, $this->command->execute(['action' => 'revoke', 'connecteur' => 'c']));
        self::assertSame(Command::SUCCESS, $this->command->execute(['action' => 'revoke', 'connecteur' => 'claude-01']));
        self::assertTrue($grants[0]->isRevoked());
        self::assertFalse($grants[1]->isRevoked());
    }
}
