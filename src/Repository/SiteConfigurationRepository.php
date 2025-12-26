<?php

namespace App\Repository;

use App\Entity\SiteConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteConfiguration>
 */
class SiteConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteConfiguration::class);
    }

    /**
     * Récupère la configuration par défaut, la crée si elle n'existe pas
     */
    public function getDefault(): SiteConfiguration
    {
        $config = $this->findOneBy(['configKey' => 'default']);

        if (!$config) {
            $config = new SiteConfiguration();
            $config->setConfigKey('default');
            $this->getEntityManager()->persist($config);
            $this->getEntityManager()->flush();
        }

        return $config;
    }

    /**
     * Récupère les settings par défaut
     */
    public function getDefaultSettings(): array
    {
        return $this->getDefault()->getSettings();
    }
}
