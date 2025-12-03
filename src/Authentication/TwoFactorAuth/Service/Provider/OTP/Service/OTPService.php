<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;

readonly class OTPService implements OTPServiceInterface
{
    public function __construct(
        private OTPValidatorInterface $otpValidator,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function validateCode(UserModel $user, string $inputCode): void
    {
        $otpData = new User(
            $user->getFieldData('OTPCODE'),
            (int) $user->getFieldData('OTPATTEMPTS'),
            new \DateTime($user->getFieldData('OTPEXPIRETIME'))
        );

        $this->otpValidator->checkLoginAttempts($otpData->getAttempts());
        $this->otpValidator->checkExpirationTime($otpData->getExpiresAt());

        try {
            $this->otpValidator->validateCode($otpData->getCode(), $inputCode);
        } catch (\Exception $e) {
            $this->userRepository->updateAttempts($user->getId(), $otpData->getAttempts() + 1);
            $user->save();
            throw $e;
        }

        $this->userRepository->resetCodeFields($user->getId());
        $user->save();
    }
}
