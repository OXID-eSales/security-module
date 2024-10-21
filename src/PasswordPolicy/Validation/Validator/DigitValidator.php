<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordDigitException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;

class DigitValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting,
        private readonly StringAnalysisServiceInterface $strAnalysisService
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordDigit();
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->strAnalysisService->hasDigitCharacter($password)) {
            throw new PasswordDigitException();
        }
    }
}
