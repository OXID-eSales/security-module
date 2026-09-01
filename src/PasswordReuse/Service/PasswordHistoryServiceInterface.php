<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\Service;

use DateTimeImmutable;

interface PasswordHistoryServiceInterface
{
    public function record(
        string $userId,
        #[\SensitiveParameter] string $supersededHash,
        ?string $rights,
        DateTimeImmutable $supersededAt
    ): void;

    public function purgeForUser(string $userId): void;
}
