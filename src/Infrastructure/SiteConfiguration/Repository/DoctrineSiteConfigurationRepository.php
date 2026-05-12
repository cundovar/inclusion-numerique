<?php

declare(strict_types=1);

namespace App\Infrastructure\SiteConfiguration\Repository;

use App\Domain\SiteConfiguration\Repository\SiteConfigurationRepositoryInterface;
use App\Entity\SiteConfiguration;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSiteConfigurationRepository implements SiteConfigurationRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
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
        return $this->getDefault()->getSettings();
    }

    public function save(SiteConfiguration $configuration): void
    {
        $this->em->persist($configuration);
        $this->em->flush();
    }
}
