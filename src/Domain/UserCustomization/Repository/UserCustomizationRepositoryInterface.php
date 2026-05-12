<?php

declare(strict_types=1);

namespace App\Domain\UserCustomization\Repository;

use App\Entity\User;
use App\Entity\UserCustomization;

interface UserCustomizationRepositoryInterface
{
    public function findByUser(User $user): ?UserCustomization;

    public function findOrCreateForUser(User $user): UserCustomization;

    public function save(UserCustomization $customization): void;
}
