<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */


namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator;

interface OTPGeneratorInterface
{
    public function generateCode(string $userId): string;
}
