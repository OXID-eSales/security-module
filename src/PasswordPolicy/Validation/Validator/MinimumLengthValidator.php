<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\EshopCommunity\Core\InputValidator;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;

class MinimumLengthValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting,
        private readonly InputValidator $inputValidator
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordMinimumLength() > 0;
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        $minimumLength = $this->moduleSetting->getPasswordMinimumLength();
        if (strlen($password) < $minimumLength) {
            throw new PasswordMinimumLengthException($minimumLength);
        }

        $shopPasswordLength = $this->inputValidator->getPasswordLength();
        if (
            $minimumLength < $shopPasswordLength &&
            strlen($password) < $shopPasswordLength
        ) {
            throw new PasswordMinimumLengthException($shopPasswordLength);
        }
    }
}
