<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Transput;

class Request implements RequestInterface
{
    public function __construct(
        protected \OxidEsales\Eshop\Core\Request $request
    ) {
    }

    public function getPassword(): string
    {
        return (string) $this->request->getRequestParameter('password');
    }
}
