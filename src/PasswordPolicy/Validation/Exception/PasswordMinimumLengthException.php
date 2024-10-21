<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

class PasswordMinimumLengthException extends PasswordValidateException
{
    public function __construct(int $minimumLength)
    {
        $this->translationParams = [$minimumLength];

        parent::__construct('ERROR_PASSWORD_MIN_LENGTH');
    }
}
