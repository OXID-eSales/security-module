<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface StringAnalysisServiceInterface
{
    public function hasUpperCaseCharacter(string $origin): bool;

    public function hasLowerCaseCharacter(string $origin): bool;

    public function hasSpecialCharacter(string $origin): bool;

    public function hasDigitCharacter(string $origin): bool;
}
