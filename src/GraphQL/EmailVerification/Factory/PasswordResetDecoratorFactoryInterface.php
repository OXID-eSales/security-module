<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\EmailVerification\Factory;

use OxidEsales\GraphQL\Storefront\Customer\Service\PasswordInterface;

interface PasswordResetDecoratorFactoryInterface
{
    public function create(PasswordInterface $originalService): PasswordInterface;
}
