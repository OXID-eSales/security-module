<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

class PasswordCollectionException extends PasswordValidateException
{
    /**
     * @var PasswordValidateException[]
     */
    private array $exceptions;

    public function __construct(array $exceptions)
    {
        parent::__construct();
        $this->exceptions = $exceptions;
    }

    /**
     * @return PasswordValidateException[]
     */
    public function getValidationExceptions(): array
    {
        return $this->exceptions;
    }
}
