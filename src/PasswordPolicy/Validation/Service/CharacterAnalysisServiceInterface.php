<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface CharacterAnalysisServiceInterface
{
    public function isControlChar(int $charCode): bool;

    public function isDigit(int $charCode): bool;

    public function isUpperCase(int $charCode): bool;

    public function isLowerCase(int $charCode): bool;

    public function isSpecialChar(int $charCode): bool;

    public function isOtherChar(int $charCode): bool;
}
