<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDto;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;

class PendingVerificationDtoFactory implements PendingVerificationDtoFactoryInterface
{
    /**
     * @param array<string, mixed> $row
     */
    public function createFromDatabaseRow(array $row): PendingVerificationDtoInterface
    {
        return new PendingVerificationDto(
            userId: (string) $row['OXUSERID'],
            expiresAt: new DateTimeImmutable((string) $row['EXPIRES_AT']),
            sendCount: (int) $row['SEND_COUNT'],
            lastSentAt: new DateTimeImmutable((string) $row['LAST_SENT_AT']),
        );
    }
}
