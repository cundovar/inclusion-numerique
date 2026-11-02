<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Mentions légales, confidentialité et conditions d'utilisation : les informations de l'éditeur viennent
 * du back-office ; tant qu'elles manquent, les pages l'affichent au lieu d'inventer.
 */
final class LegalPagesTest extends WebTestCase
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

    public function testEmptyInformationIsShownAsToCompleteWithoutTodaysDate(): void
    {
        foreach (['/mentions-legales', '/privacy', '/terms'] as $url) {
            $crawler = $this->client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
            self::assertGreaterThan(0, $crawler->filter('.a-completer')->count(), $url);
            self::assertStringNotContainsString((new \DateTimeImmutable())->format('d/m/Y'), $crawler->filter('.legal-pied')->text(), 'La date ne suit plus le jour de consultation');
            self::assertSame('← Retour au site', $crawler->filter('.legal-retour')->text());
            self::assertStringNotContainsString("Retour à l'inscription", (string) $this->client->getResponse()->getContent());
        }

        self::assertStringContainsString('OVH SAS', $this->client->request('GET', '/mentions-legales')->filter('.legal-container')->text());
    }

    public function testInformationEnteredInTheBackOfficeAppearsOnThePages(): void
    {
        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin, 'main');

        // Le tableau de bord signale ce qui manque
        $this->client->request('GET', '/admin');
        self::assertSelectorTextContains('.alert-warning', 'Informations légales à compléter');

        $crawler = $this->client->request('GET', '/admin/site-config');
        if ($this->client->getResponse()->isRedirect()) {
            $crawler = $this->client->followRedirect();
        }
        $form = $crawler->filter('form[name="SiteConfiguration"]')->form();
        $this->client->submit($form, [
            'SiteConfiguration[legalEditeur]' => 'Association Exemple',
            'SiteConfiguration[legalAdresse]' => '1 rue Exemple, 75000 Paris',
            'SiteConfiguration[legalEmail]' => 'contact@example.org',
            'SiteConfiguration[legalDirecteurPublication]' => 'Camille Exemple',
            'SiteConfiguration[legalIdentifiant]' => 'W000000000',
            'SiteConfiguration[legalMiseAJour]' => '2026-10-20',
        ]);
        self::assertTrue($this->client->getResponse()->isRedirect(), 'Le formulaire est enregistré');

        $this->entityManager->clear();
        $config = static::getContainer()->get(SiteConfigurationRepositoryInterface::class)->getDefault();
        self::assertSame('Médiation numérique', $config->getSettings()['siteName'], 'Les autres paramètres sont gardés');
        self::assertStringNotContainsString('legal', $config->getSettingsJson(), 'Les informations légales ne passent pas par le JSON');

        $page = $this->client->request('GET', '/mentions-legales')->filter('.legal-container');
        self::assertSame(0, $page->filter('.a-completer')->count());
        foreach (['Association Exemple', '1 rue Exemple, 75000 Paris', 'W000000000', 'contact@example.org', 'Camille Exemple', '20/10/2026'] as $value) {
            self::assertStringContainsString($value, $page->text());
        }

        $privacy = $this->client->request('GET', '/privacy')->filter('.privacy-container')->text();
        self::assertStringContainsString('Le responsable du traitement de vos données est Association Exemple', $privacy);
        self::assertStringContainsString('CNIL', $privacy);

        $this->client->request('GET', '/admin');
        self::assertSelectorNotExists('.alert-warning');
    }
}
