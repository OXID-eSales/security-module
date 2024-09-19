<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordLowerCaseException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;

class LowerCaseValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting,
        private readonly StringAnalysisServiceInterface $stringAnalysisService
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordLowercase();
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->stringAnalysisService->hasLowerCaseCharacter($password)) {
            throw new PasswordLowerCaseException();
        }
    }
}
