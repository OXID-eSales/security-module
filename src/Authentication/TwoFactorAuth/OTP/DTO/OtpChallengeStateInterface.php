<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO;

use DateTimeImmutable;

interface OtpChallengeStateInterface
{
    public function getUserId(): string;

    public function getCodeHash(): string;

    public function getAttempts(): int;

    public function getLastSentAt(): ?DateTimeImmutable;

    public function getExpiresAt(): DateTimeImmutable;

    public function getVerifiedAt(): ?DateTimeImmutable;
}
