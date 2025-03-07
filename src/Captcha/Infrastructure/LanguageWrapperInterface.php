<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Infrastructure;

interface LanguageWrapperInterface
{
    public function getCurrentLanguageAbbr(): string;
}
