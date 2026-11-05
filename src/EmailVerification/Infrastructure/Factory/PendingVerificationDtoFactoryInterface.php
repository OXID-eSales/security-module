<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory;

use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;

interface PendingVerificationDtoFactoryInterface
{
    /**
     * @param array<string, mixed> $row
     */
    public function createFromDatabaseRow(array $row): PendingVerificationDtoInterface;
}
