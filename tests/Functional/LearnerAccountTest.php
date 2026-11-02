<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Page;
use App\Entity\PageContent;
use App\Entity\QCM;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Compte apprenant facultatif : inscription par pseudo, espace personnel avec les contenus réservés,
 * changement de mot de passe et suppression du compte.
 */
final class LearnerAccountTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testRegistrationCreatesAPlainLearnerAndOpensTheirSpace(): void
    {
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => ' Jo@Exemple.fr ',
            'registration_form[username]' => 'mamie_jo',
            'registration_form[plainPassword][first]' => 'un-bon-mot-de-passe',
            'registration_form[plainPassword][second]' => 'un-bon-mot-de-passe',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseRedirects('/spa/mon-espace');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'mamie_jo']);
        self::assertNotNull($user);
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame('jo@exemple.fr', $user->getEmail());
        self::assertNotSame('un-bon-mot-de-passe', $user->getPassword());

        $this->client->request('GET', '/user-api/mon-espace');
        self::assertResponseIsSuccessful();
        self::assertSame(['pseudo' => 'mamie_jo', 'email' => 'jo@exemple.fr', 'cours' => [], 'parcours' => [], 'exercices' => []], $this->json());
    }

    public function testRegistrationRejectsAFullNameOrAShortPassword(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'pas-une-adresse',
            'registration_form[username]' => 'Jeanne Dupont',
            'registration_form[plainPassword][first]' => 'court',
            'registration_form[plainPassword][second]' => 'court',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseStatusCodeSame(422);
        $html = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('sans espace', $html);
        self::assertStringContainsString('au moins 8 caractères', $html);
        self::assertStringContainsString('pas valide', $html);
        self::assertSame(0, $this->entityManager->getRepository(User::class)->count([]));
    }

    public function testRegistrationRefusesAnAddressAlreadyUsed(): void
    {
        $this->learner('alice');
        $this->entityManager->flush();

        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'ALICE@exemple.fr',
            'registration_form[username]' => 'alice2',
            'registration_form[plainPassword][first]' => 'un-bon-mot-de-passe',
            'registration_form[plainPassword][second]' => 'un-bon-mot-de-passe',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('Un compte existe déjà avec cette adresse', (string) $this->client->getResponse()->getContent());
    }

    public function testLearnerSignsInWithTheirAddressAndIsShownByPseudo(): void
    {
        $this->learner('alice');
        $this->entityManager->flush();

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', ['username' => 'Alice@Exemple.fr', 'password' => 'mot-de-passe-alice']);
        self::assertResponseRedirects();

        $this->client->request('GET', '/user-api/me');
        self::assertResponseIsSuccessful();
        self::assertSame('alice', $this->json()['username']);
        self::assertArrayNotHasKey('email', $this->json());
    }

    public function testBackOfficeAccountWithoutAddressStillSignsInWithItsPseudo(): void
    {
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($admin, 'mot-de-passe-admin'));
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', ['username' => 'redaction', 'password' => 'mot-de-passe-admin']);

        $this->client->request('GET', '/user-api/me');
        self::assertResponseIsSuccessful();
    }

    public function testSpaceListsOnlyThePublishedContentReservedToTheLearner(): void
    {
        $alice = $this->learner('alice');
        $bruno = $this->learner('bruno');
        $parcours = (new Category())->setName('Atelier Alice')->setReserve(true)->addDestinataire($alice);
        $this->entityManager->persist($parcours);
        $this->course('pour-alice', PageContent::STATUS_PUBLISHED)->setReserve(true)->addDestinataire($alice);
        $this->course('brouillon-pour-alice', PageContent::STATUS_DRAFT)->setReserve(true)->addDestinataire($alice);
        $this->course('pour-bruno', PageContent::STATUS_PUBLISHED)->setReserve(true)->addDestinataire($bruno);
        $this->course('public', PageContent::STATUS_PUBLISHED);
        $this->entityManager->persist((new QCM())->setTitre('<p>Exercice pour Alice</p>')->setSolution('s')->setReserve(true)->addDestinataire($alice));
        $this->entityManager->flush();

        $this->client->loginUser($alice, 'main');
        $this->client->request('GET', '/user-api/mon-espace');

        $space = $this->json();
        self::assertSame(['pour-alice'], array_column($space['cours'], 'slug'));
        self::assertSame(['Atelier Alice'], array_column($space['parcours'], 'nom'));
        self::assertSame(['Exercice pour Alice'], array_column($space['exercices'], 'question'));

        // Série « Mes exercices » côté site
        $this->client->request('GET', '/api/q_c_ms', ['reserve' => 'true'], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertSame(['<p>Exercice pour Alice</p>'], array_column($this->json()['member'], 'titre'));
    }

    public function testVisitorHasNoSpace(): void
    {
        $this->client->request('GET', '/user-api/mon-espace');

        self::assertResponseStatusCodeSame(401);
    }

    public function testPasswordChangeRequiresTheCurrentPassword(): void
    {
        $alice = $this->learner('alice');
        $this->entityManager->flush();
        $this->client->loginUser($alice, 'main');

        $this->send('POST', '/user-api/mon-espace/mot-de-passe', ['motDePasseActuel' => 'faux', 'nouveauMotDePasse' => 'nouveau-mot-de-passe']);
        self::assertResponseStatusCodeSame(403);

        $this->send('POST', '/user-api/mon-espace/mot-de-passe', ['motDePasseActuel' => 'mot-de-passe-alice', 'nouveauMotDePasse' => 'court']);
        self::assertResponseStatusCodeSame(422);

        $this->send('POST', '/user-api/mon-espace/mot-de-passe', ['motDePasseActuel' => 'mot-de-passe-alice', 'nouveauMotDePasse' => 'nouveau-mot-de-passe']);
        self::assertResponseIsSuccessful();
        $this->entityManager->clear();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($this->entityManager->find(User::class, $alice->getId()), 'nouveau-mot-de-passe'));
    }

    public function testAccountDeletionRemovesTheLearnerAndTheirAccesses(): void
    {
        $alice = $this->learner('alice');
        $course = $this->course('pour-alice', PageContent::STATUS_PUBLISHED)->setReserve(true)->addDestinataire($alice);
        $this->entityManager->flush();
        $this->client->loginUser($alice, 'main');

        // Une requête de formulaire (possible depuis un autre site) est refusée
        $this->client->request('DELETE', '/user-api/mon-espace', ['motDePasse' => 'mot-de-passe-alice']);
        self::assertResponseStatusCodeSame(415);

        $this->send('DELETE', '/user-api/mon-espace', ['motDePasse' => 'mot-de-passe-alice']);
        self::assertResponseIsSuccessful();

        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(User::class, $alice->getId()));
        $course = $this->entityManager->find(PageContent::class, $course->getId());
        self::assertTrue($course?->isReserve(), 'Le cours reste réservé, sans destinataire');
        self::assertCount(0, $course->getDestinataires());
    }

    public function testAdministratorCannotDeleteTheirAccountFromTheSpace(): void
    {
        $admin = $this->learner('redaction')->setRoles(['ROLE_ADMIN']);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');

        $this->send('DELETE', '/user-api/mon-espace', ['motDePasse' => 'mot-de-passe-redaction']);

        self::assertResponseStatusCodeSame(403);
    }

    private function learner(string $name): User
    {
        $user = (new User())->setUsername($name)->setEmail($name.'@exemple.fr');
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'mot-de-passe-'.$name));
        $this->entityManager->persist($user);

        return $user;
    }

    private function course(string $title, string $status): PageContent
    {
        $page = (new Page())->setSlug('/'.$title);
        $course = (new PageContent())->setTitle($title)->setStatus($status)->setPage($page);
        $this->entityManager->persist($page);
        $this->entityManager->persist($course);

        return $course;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function send(string $method, string $url, array $payload): void
    {
        $this->client->request($method, $url, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload, \JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private function json(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true);
    }
}
