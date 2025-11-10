<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Factory;

use OxidEsales\Eshop\Application\Model\User;

class UserFactory implements UserFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(): User
    {
        return oxNew(User::class);
    }
}
