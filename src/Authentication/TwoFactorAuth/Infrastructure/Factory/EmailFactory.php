<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Core\Email;

class EmailFactory implements EmailFactoryInterface
{
    public function create(): Email
    {
        return oxNew(Email::class);
    }
}
