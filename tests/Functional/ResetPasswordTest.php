<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Mot de passe oublié : lien à usage unique valable une heure, réponse identique que l'adresse existe ou non.
 */
final class ResetPasswordTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $alice;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->alice = (new User())->setUsername('alice')->setEmail('alice@exemple.fr');
        $this->alice->setPassword($this->hasher()->hashPassword($this->alice, 'ancien-mot-de-passe'));
        $this->entityManager->persist($this->alice);
        $this->entityManager->flush();
    }

    public function testLinkReceivedByEmailSetsANewPasswordOnce(): void
    {
        $this->ask('Alice@Exemple.fr');
        self::assertSelectorTextContains('[role=status]', 'Si un compte existe avec cette adresse');
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertSame('alice@exemple.fr', $email->getTo()[0]->getAddress());
        self::assertStringContainsString('Bonjour alice', (string) $email->getTextBody());
        $link = $this->linkIn($email);

        // Le jeton quitte aussitôt l'adresse de la page
        $this->client->request('GET', $link);
        self::assertResponseRedirects('/mot-de-passe-oublie/nouveau');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Enregistrer', ['password' => 'court', 'password_confirm' => 'court']);
        self::assertResponseStatusCodeSame(422);
        $this->client->submitForm('Enregistrer', ['password' => 'nouveau-mot-de-passe', 'password_confirm' => 'autre-mot-de-passe']);
        self::assertResponseStatusCodeSame(422);
        $this->client->submitForm('Enregistrer', ['password' => 'nouveau-mot-de-passe', 'password_confirm' => 'nouveau-mot-de-passe']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[role=status]', 'Votre mot de passe a été changé');

        $this->entityManager->clear();
        self::assertTrue($this->hasher()->isPasswordValid($this->entityManager->find(User::class, $this->alice->getId()), 'nouveau-mot-de-passe'));
        self::assertSame(0, $this->entityManager->getRepository(PasswordResetToken::class)->count([]));

        // Le même lien ne sert pas deux fois
        $this->client->request('GET', $link);
        $this->client->followRedirect();
        self::assertResponseStatusCodeSame(410);
    }

    public function testUnknownAddressGetsTheSameAnswerAndNoEmail(): void
    {
        $this->ask('personne@exemple.fr');

        self::assertSelectorTextContains('[role=status]', 'Si un compte existe avec cette adresse');
        self::assertEmailCount(0);
    }

    public function testRepeatedRequestsSendOnlyOneEmailEveryFiveMinutes(): void
    {
        $this->ask('alice@exemple.fr');
        $this->ask('alice@exemple.fr');

        self::assertEmailCount(0, message: 'La deuxième demande ne part pas');
        self::assertSame(1, $this->entityManager->getRepository(PasswordResetToken::class)->count([]));
    }

    public function testExpiredLinkIsRefused(): void
    {
        $this->entityManager->persist(new PasswordResetToken($this->alice, hash('sha256', 'jeton-perime'), new \DateTimeImmutable('-1 minute')));
        $this->entityManager->flush();

        $this->client->request('GET', '/mot-de-passe-oublie/nouveau/jeton-perime');
        $this->client->followRedirect();

        self::assertResponseStatusCodeSame(410);
        self::assertSelectorTextContains('[role=alert]', 'Ce lien ne fonctionne plus');
    }

    public function testLoginPageOffersTheForgottenPasswordLink(): void
    {
        $this->client->request('GET', '/login');

        self::assertSelectorExists('a[href="/mot-de-passe-oublie"]');
    }

    private function ask(string $email): void
    {
        $this->client->request('GET', '/mot-de-passe-oublie');
        $this->client->submitForm('Recevoir le lien', ['email' => $email]);
        self::assertResponseIsSuccessful();
    }

    private function linkIn(Email $email): string
    {
        self::assertSame(1, preg_match('#https?://[^/\s]+(/mot-de-passe-oublie/nouveau/[A-Za-z0-9_-]+)#', (string) $email->getTextBody(), $match));

        return $match[1];
    }

    private function hasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get(UserPasswordHasherInterface::class);
    }
}
