<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Intrastructure;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

class ExceptionFactory implements ExceptionFactoryInterface
{
    public function __construct(
        private readonly \OxidEsales\Eshop\Core\Language $language
    ) {
    }

    public function create(PasswordValidateException $exception): InputException
    {
        $exception = oxNew(
            InputException::class,
            sprintf(
                /** @phpstan-ignore-next-line */
                $this->language->translateString($exception->getMessage()),
                ...$exception->getTranslationParameters()
            )
        );

        return $exception;
    }
}
