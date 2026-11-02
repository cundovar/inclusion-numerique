<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * L'apparence du site est lisible par tout visiteur, sans compte, et ne se modifie pas par l'API.
 */
final class SiteConfigurationApiTest extends WebTestCase
{
    public function testAnonymousVisitorReadsTheSiteConfiguration(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $client->request('GET', '/api/site-configuration');

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('settings', json_decode((string) $client->getResponse()->getContent(), true));

        $client->request('POST', '/api/site-configuration');
        self::assertResponseStatusCodeSame(405);
    }
}
