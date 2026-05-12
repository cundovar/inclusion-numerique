<?php

declare(strict_types=1);

namespace App\Domain\SiteConfiguration\Repository;

use App\Entity\SiteConfiguration;

interface SiteConfigurationRepositoryInterface
{
    public function getDefault(): SiteConfiguration;

    public function getDefaultSettings(): array;

    public function save(SiteConfiguration $configuration): void;
}
