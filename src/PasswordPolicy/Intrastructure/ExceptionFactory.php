<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Intrastructure;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordCollectionException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

class ExceptionFactory implements ExceptionFactoryInterface
{
    public function __construct(
        private readonly \OxidEsales\Eshop\Core\Language $language
    ) {
    }

    public function create(PasswordValidateException $exception): InputException
    {
        if ($exception instanceof PasswordCollectionException) {
            return $this->createCollection($exception);
        }

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

    private function createCollection(PasswordCollectionException $collection): InputException
    {
        $lines = [];
        foreach ($collection->getValidationExceptions() as $ex) {
            $lines[] = sprintf(
                /** @phpstan-ignore-next-line */
                $this->language->translateString($ex->getMessage()),
                ...$ex->getTranslationParameters()
            );
        }
        /** @phpstan-ignore-next-line */
        $message = '<ul><li>' . implode('</li><li>', $lines) . '</li></ul>';

        return oxNew(InputException::class, $message);
    }
}
