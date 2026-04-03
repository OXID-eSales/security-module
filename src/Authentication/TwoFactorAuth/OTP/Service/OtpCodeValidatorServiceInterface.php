<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;

interface OtpCodeValidatorServiceInterface
{
    /**
     * @throws InvalidCodeException
     */
    public function validateCode(
        string $userId,
        #[\SensitiveParameter] string $inputCode
    ): void;
}
