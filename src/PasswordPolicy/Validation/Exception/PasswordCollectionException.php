<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

use OxidEsales\Eshop\Core\Exception\InputException;

class PasswordCollectionException extends InputException
{
    /**
     * @var PasswordValidateException[]
     */
    private array $exceptions;

    public function __construct(array $exceptions)
    {
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
