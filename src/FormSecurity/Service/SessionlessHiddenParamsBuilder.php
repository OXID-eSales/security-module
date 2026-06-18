<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\FormSecurity\Service;

use OxidEsales\Eshop\Core\Registry;

class SessionlessHiddenParamsBuilder implements SessionlessHiddenParamsBuilderInterface
{
    public function build(string $additionalParams): string
    {
        $value = '';

        $lang = $this->getFormLang();
        if ($lang !== '') {
            $value .= "\n{$lang}";
        }

        $value .= $additionalParams;

        return $value;
    }

    protected function getFormLang(): string
    {
        return Registry::getLang()->getFormLang();
    }
}
