<?php

declare(strict_types=1);

namespace App\Infrastructure\SiteConfiguration\Repository;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\SiteConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DoctrineSiteConfigurationRepository implements SiteConfigurationRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        // La configuration est partagée avec mediateur dans la même base : le nom propre au site l'emporte
        #[Autowire('%app.site_name%')]
        private string $siteName = 'Inclusion numérique',
    ) {}

    public function getDefault(): SiteConfiguration
    {
        $config = $this->em->getRepository(SiteConfiguration::class)->findOneBy(['configKey' => 'default']);

        if (!$config) {
            $config = new SiteConfiguration();
            $config->setConfigKey('default');
            $this->save($config);
        }

        return $config;
    }

    public function getDefaultSettings(): array
    {
        return ['siteName' => $this->siteName] + $this->getDefault()->getSettings();
    }

    public function save(SiteConfiguration $configuration): void
    {
        $this->em->persist($configuration);
        $this->em->flush();
    }
}
