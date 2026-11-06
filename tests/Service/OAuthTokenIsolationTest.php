<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\OAuth\OAuthServer;
use PHPUnit\Framework\TestCase;

/**
 * Les tables OAuth sont partagées avec mediateur, qui stocke sha256(jeton) :
 * les empreintes de ce site doivent différer pour que les jetons ne passent pas d'un site à l'autre.
 */
final class OAuthTokenIsolationTest extends TestCase
{
    public function testHashDiffersFromTheOneStoredByTheOtherSite(): void
    {
        $token = 'jeton-de-test';

        self::assertNotSame(hash('sha256', $token), OAuthServer::hash($token));
    }

    public function testHashFitsTheSharedColumnsAndIsStable(): void
    {
        $hash = OAuthServer::hash('jeton-de-test');

        self::assertSame(64, \strlen($hash));
        self::assertSame($hash, OAuthServer::hash('jeton-de-test'));
    }
}
