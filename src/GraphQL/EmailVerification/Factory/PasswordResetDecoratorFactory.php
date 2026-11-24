<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\EmailVerification\Factory;

use OxidEsales\GraphQL\Storefront\Customer\Service\PasswordInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\GraphQL\EmailVerification\Decorator\PasswordResetDecorator;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;

/**
 * Creates the decorator at runtime, so the container never has to load the decorator class
 * while compiling. This keeps the module working when graphql-storefront is not installed.
 */
class PasswordResetDecoratorFactory implements PasswordResetDecoratorFactoryInterface
{
    public function __construct(
        private readonly UserModelFactoryInterface $userModelFactory,
        private readonly UserEmailVerificationFacadeInterface $verificationFacade,
    ) {
    }

    public function create(PasswordInterface $originalService): PasswordInterface
    {
        return new PasswordResetDecorator(
            originalService: $originalService,
            userModelFactory: $this->userModelFactory,
            verificationFacade: $this->verificationFacade,
        );
    }
}
