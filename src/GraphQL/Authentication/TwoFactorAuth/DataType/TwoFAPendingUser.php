<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\DataType;

use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Base\DataType\UserInterface;
use TheCodingMachine\GraphQLite\Types\ID;

final class TwoFAPendingUser implements UserInterface
{
    public function __construct(private readonly EshopUserModel $userModel)
    {
    }

    public function email(): string
    {
        return (string)$this->userModel->getRawFieldData('oxusername');
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function id(): ID
    {
        return new ID((string)$this->userModel->getId());
    }

    public function isAnonymous(): bool
    {
        return true;
    }
}
