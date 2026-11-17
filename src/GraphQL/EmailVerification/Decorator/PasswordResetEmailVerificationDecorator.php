<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\EmailVerification\Decorator;

use OxidEsales\GraphQL\Storefront\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Storefront\Customer\Service\PasswordInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;

/**
 * The password reset link goes to the registered address, so a successful reset over OXAPI
 * confirms that address the same way the storefront reset does.
 */
class PasswordResetEmailVerificationDecorator implements PasswordInterface
{
    public function __construct(
        private readonly PasswordInterface $originalService,
        private readonly UserModelFactoryInterface $userModelFactory,
        private readonly UserEmailVerificationFacadeInterface $verificationFacade,
    ) {
    }

    public function change(string $old, string $new): CustomerDataType
    {
        return $this->originalService->change($old, $new);
    }

    public function sendPasswordForgotEmail(string $email): bool
    {
        return $this->originalService->sendPasswordForgotEmail($email);
    }

    public function resetPasswordByUpdateHash(string $updateHash, string $newPassword, string $repeatPassword): bool
    {
        $userId = $this->findUserIdByUpdateHash($updateHash);
        $result = $this->originalService->resetPasswordByUpdateHash($updateHash, $newPassword, $repeatPassword);

        if ($result && $userId !== null) {
            $this->verificationFacade->confirmEmailAfterPasswordReset($userId);
        }

        return $result;
    }

    private function findUserIdByUpdateHash(string $updateHash): ?string
    {
        $user = $this->userModelFactory->create();
        $user->loadUserByUpdateId($updateHash);

        return $user->isLoaded() ? (string) $user->getId() : null;
    }
}
