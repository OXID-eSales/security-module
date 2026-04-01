<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpCodeValidatorService implements OtpCodeValidatorServiceInterface
{
    public function __construct(
        private OtpChallengeStateRepositoryInterface $repository,
    ) {
    }

    public function validateCode(
        string $userId,
        #[\SensitiveParameter] string $inputCode
    ): void {
        // todo-critical: implement
    }
}
