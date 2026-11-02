<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * Fonctions de DevDoc écartées (pas de suivi, de favoris ni de notes, pas d'exercices de code ni d'agents
 * de génération) : aucune route ne doit les réexposer. L'inscription est revenue avec les contenus personnalisés.
 */
final class RemovedFeaturesTest extends WebTestCase
{
    /**
     * @dataProvider removedPathPrefixes
     */
    public function testNoRouteExposesARemovedFeature(string $prefix): void
    {
        self::bootKernel();
        $paths = array_map(
            static fn ($route) => $route->getPath(),
            static::getContainer()->get(RouterInterface::class)->getRouteCollection()->all(),
        );

        $matches = array_filter($paths, static fn (string $path) => str_starts_with($path, $prefix));

        self::assertSame([], array_values($matches));
    }

    public static function removedPathPrefixes(): iterable
    {
        yield 'favoris' => ['/api/me-favorites'];
        yield 'notes' => ['/api/notes'];
        yield 'suivi des visites' => ['/api/page-visits'];
        yield 'personnalisation par visiteur' => ['/api/customization'];
        yield 'résultats de QCM enregistrés' => ['/api/qcm-results'];
        yield 'propositions IA' => ['/api/proposition-ia'];
        yield 'API admin par clé' => ['/api/admin'];
        yield 'exercices de code' => ['/api/exo'];
        yield 'documentation de code' => ['/api/doc_de_codes'];
        yield 'API de rédaction par clé (remplacée par le connecteur /mcp)' => ['/api/redaction'];
    }
}
