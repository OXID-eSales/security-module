<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Intrastructure;

use OxidEsales\EshopCommunity\Core\Exception\InputException;

interface ExceptionFactoryInterface
{
    public function create(string $message): InputException;
}
