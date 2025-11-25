<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserDTO;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;
use DateTimeImmutable;

readonly class OTPService implements OTPServiceInterface
{
    public function __construct(
        private OTPValidatorInterface $otpValidator,
    ) {
    }

    public function validateCode(UserModel $user, string $inputCode): void
    {
        $otpData = new UserDTO(
            $user->getFieldData('OTPCODE'),
            (int) $user->getFieldData('OTPATTEMPTS'),
            new \DateTime($user->getFieldData('OTPEXPIRETIME'))
        );

        $this->otpValidator->checkLoginAttempts($otpData->getAttempts());
        $this->otpValidator->checkExpirationTime($otpData->getExpiresAt());

        try {
            $this->otpValidator->validateCode($otpData->getCode(), $inputCode);
        } catch (\Exception $e) {
            // todo: got to repository
            $user->assign([
                'OTPATTEMPTS' => $otpData->getAttempts() + 1
            ]);
            $user->save();
            throw $e;
        }

        // todo: got to repository
        $user->assign([
            'OTPCODE'       => '',
            'OTPEXPIRETIME' => 0,
            'OTPATTEMPTS'   => 0,
        ]);
        $user->save();
    }
}
