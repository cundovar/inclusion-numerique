<?php

declare(strict_types=1);

namespace App\Tests\Functional\Mcp;

use App\Entity\Category;

/**
 * Grandes rubriques au-dessus des parcours (rubrique → parcours → modules → cours) : création sans doublon,
 * classement des parcours et lecture dans le catalogue.
 */
final class RubriqueConnectorTest extends McpTestCase
{
    public function testParcoursAreGroupedUnderRubriques(): void
    {
        $rubrique = $this->tool(self::CLAUDE, 'creer_rubrique', ['nom' => 'Automatiser et gérer les données']);
        self::assertFalse($rubrique['error'], $rubrique['text']);
        $rubriqueId = $rubrique['data']['id'];
        self::assertStringContainsString('identifiant '.$rubriqueId, $this->tool(self::CLAUDE, 'creer_rubrique', ['nom' => 'Automatiser et gérer les données'])['text']);

        // Un parcours peut naître dans sa rubrique, ou y être rangé ensuite
        $n8n = $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => 'Automatisation avec n8n', 'rubriqueId' => $rubriqueId]);
        self::assertFalse($n8n['error'], $n8n['text']);
        self::assertSame($rubriqueId, $n8n['data']['rubriqueId']);
        $donnees = $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => 'Stockage et organisation des données'])['data']['id'];

        $classes = $this->tool(self::CLAUDE, 'classer_parcours', ['parcoursIds' => [$donnees, 999999], 'rubriqueId' => $rubriqueId]);
        self::assertFalse($classes['error'], $classes['text']);
        self::assertSame([['id' => $donnees, 'nom' => 'Stockage et organisation des données', 'ancienneRubriqueId' => null, 'nouvelleRubriqueId' => $rubriqueId]], $classes['data']['classes']);
        self::assertSame([999999], array_column($classes['data']['refus'], 'id'));

        $catalogue = $this->tool(self::CLAUDE, 'lister_catalogue')['data'];
        self::assertSame([['id' => $rubriqueId, 'nom' => 'Automatiser et gérer les données', 'ordre' => 1]], $catalogue['rubriques']);
        self::assertSame([$rubriqueId, $rubriqueId], array_column($catalogue['parcours'], 'rubriqueId'));

        // null retire un parcours de sa rubrique
        $this->tool(self::CLAUDE, 'classer_parcours', ['parcoursIds' => [$donnees], 'rubriqueId' => null]);
        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Category::class, $donnees)->getSuperMenu());
    }

    public function testRubriquesAreRankedForTheHomePage(): void
    {
        $donnees = $this->tool(self::CLAUDE, 'creer_rubrique', ['nom' => 'Automatiser et gérer les données'])['data'];
        $decouvrir = $this->tool(self::CLAUDE, 'creer_rubrique', ['nom' => 'Découvrir et utiliser l\'IA'])['data'];
        self::assertSame([1, 2], [$donnees['ordre'], $decouvrir['ordre']]);

        $ranked = $this->tool(self::CLAUDE, 'ordonner_rubriques', ['rubriqueIds' => [$decouvrir['id'], $donnees['id'], 999999]]);
        self::assertFalse($ranked['error'], $ranked['text']);
        self::assertSame([[$decouvrir['id'], 1], [$donnees['id'], 2]], array_map(static fn (array $o) => [$o['id'], $o['ordre']], $ranked['data']['ordres']));
        self::assertSame([999999], array_column($ranked['data']['refus'], 'id'));

        // Le rang est servi au site avec chaque parcours
        $parcours = $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => 'Découvrir l\'IA', 'rubriqueId' => $decouvrir['id']])['data'];
        $this->client->request('GET', '/api/categories/'.$parcours['id'], [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        $superMenu = json_decode((string) $this->client->getResponse()->getContent(), true)['superMenu'];
        self::assertSame([$decouvrir['id'], 'Découvrir et utiliser l\'IA', 1], [$superMenu['id'], $superMenu['name'], $superMenu['ordre']]);
    }

    public function testUnknownRubriqueIsRejected(): void
    {
        self::assertStringContainsString('Rubrique introuvable', $this->tool(self::CLAUDE, 'creer_parcours', ['nom' => 'Orphelin', 'rubriqueId' => 999999])['text']);
        self::assertStringContainsString('Rubrique introuvable', $this->tool(self::CLAUDE, 'classer_parcours', ['parcoursIds' => [1], 'rubriqueId' => 999999])['text']);
        self::assertTrue($this->tool(self::CLAUDE, 'creer_rubrique', ['nom' => ''])['error']);
    }
}
