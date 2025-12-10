<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface AuthorizeServiceInterface
{
    public function validate(string $inputCode): void;

    public function generate(): void;
}
