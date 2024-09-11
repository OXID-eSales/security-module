<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

class PasswordValidateException extends \Exception
{
    protected array $translationParameters = [];

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }
}
