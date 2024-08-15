<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorType;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\PasswordValidatorInterface;

class PasswordValidatorChain implements PasswordValidatorChainInterface
{
    /**
     * @param iterable<PasswordValidatorInterface> $validators
     */
    public function __construct(
        private iterable $validators
    ) {
        foreach ($this->validators as $validator) {
            if (!$validator instanceof PasswordValidatorInterface) {
                throw new InvalidValidatorType();
            }
        }
    }

    public function validatePassword(#[\SensitiveParameter] string $password): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($password);
        }
    }
}
