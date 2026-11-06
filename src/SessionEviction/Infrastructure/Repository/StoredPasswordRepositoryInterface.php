<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Infrastructure\Repository;

interface StoredPasswordRepositoryInterface
{
    public function getPasswordHash(string $userId): string;
}
