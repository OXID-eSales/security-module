<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

interface OtpCodeHasherServiceInterface
{
    public function hash(#[\SensitiveParameter] string $code): string;
}
