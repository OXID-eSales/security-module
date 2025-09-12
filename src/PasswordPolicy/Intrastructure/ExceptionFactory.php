<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Intrastructure;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordCollectionException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

readonly class ExceptionFactory implements ExceptionFactoryInterface
{
    public function __construct(
        private Language $language
    ) {
    }

    public function create(PasswordValidateException $exception): InputException
    {
        if ($exception instanceof PasswordCollectionException) {
            return $this->createCollection($exception);
        }

        return oxNew(InputException::class, $this->formatExceptionMessage($exception));
    }

    private function createCollection(PasswordCollectionException $collection): InputException
    {
        $lines = [];
        foreach ($collection->getValidationExceptions() as $exception) {
            $lines[] = $this->formatExceptionMessage($exception);
        }
        $message = '<ul><li>' . implode('</li><li>', $lines) . '</li></ul>';

        return oxNew(InputException::class, $message);
    }

    private function formatExceptionMessage(PasswordValidateException $exception): string
    {
        return sprintf(
            /** @phpstan-ignore-next-line */
            $this->language->translateString($exception->getMessage()),
            ...$exception->getTranslationParameters()
        );
    }
}
