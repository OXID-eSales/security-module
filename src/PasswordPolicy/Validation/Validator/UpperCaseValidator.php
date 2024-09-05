<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordUpperCaseException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysisServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;

class UpperCaseValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting,
        private readonly StringAnalysisServiceInterface $stringAnalysisService,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordUppercase();
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->stringAnalysisService->hasUpperCaseCharacter($password)) {
            throw new PasswordUpperCaseException();
        }
    }
}
