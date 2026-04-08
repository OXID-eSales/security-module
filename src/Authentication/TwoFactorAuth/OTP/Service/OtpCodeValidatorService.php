<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpCodeValidatorService implements OtpCodeValidatorServiceInterface
{
    public function __construct(
        /** @phpstan-ignore property.onlyWritten */
        private OtpChallengeStateRepositoryInterface $repository,
    ) {
    }

    public function validateCode(
        string $userId,
        #[\SensitiveParameter] string $inputCode
    ): void {
        // todo-critical: implement, also remove the phpstan-ignore then
    }
}
