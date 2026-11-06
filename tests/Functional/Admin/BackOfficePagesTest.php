<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * Chaque liste et chaque formulaire de création disponible du back-office s'ouvre pour le responsable éditorial.
 * Protège contre un champ qui viserait encore une entité retirée de DevDoc.
 */
final class BackOfficePagesTest extends WebTestCase
{
    public function testEveryEnabledCrudIndexAndNewFormRenders(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $paths = [];
        foreach (static::getContainer()->get(RouterInterface::class)->getRouteCollection() as $name => $route) {
            if (preg_match('/^dashboard_\w+_(index|new)$/', $name) === 1 && $name !== 'dashboard_qcm_draft_new') {
                $paths[] = $route->getPath();
            }
        }
        self::assertNotEmpty($paths);

        foreach ($paths as $path) {
            $client->request('GET', $path);
            self::assertTrue($client->getResponse()->isSuccessful(), sprintf('%s répond %d', $path, $client->getResponse()->getStatusCode()));
        }
    }

    public function testConnectorQcmDraftAppearsInTheRealBackOffice(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $admin = (new User())->setUsername('redaction')->setRoles(['ROLE_ADMIN'])->setPassword('inutilise');
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $draft = (new \App\Entity\QcmDraft('connecteur:Claude#test'))
            ->setQuestion('Quel est le premier geste à faire ?')
            ->setExplanation('Prenez le temps de vérifier.')
            ->setAnswers([
                ['texte' => 'Vérifier la source', 'correcte' => true, 'explication' => null],
                ['texte' => 'Cliquer immédiatement', 'correcte' => false, 'explication' => 'Le lien peut être frauduleux.'],
            ]);
        $entityManager->persist($draft);
        $entityManager->flush();

        $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();
        $client->clickLink('Brouillons du connecteur');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Quel est le premier geste à faire ?');
    }
}
