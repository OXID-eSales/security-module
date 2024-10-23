<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface PasswordStrengthServiceInterface
{
    public function estimateStrength(string $password): int;
}
