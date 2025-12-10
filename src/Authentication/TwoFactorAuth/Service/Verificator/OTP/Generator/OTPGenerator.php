<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator;

use DateTime;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

readonly class OTPGenerator implements OTPGeneratorInterface
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRATION = 300; // 5 minutes

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function generateCode(string $userId): string
    {
        $OTPCode = str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
        $expiresAt = (new DateTime())->setTimestamp(time() + self::OTP_EXPIRATION);
        $this->userRepository->addOTPtoUser($userId, $OTPCode, $expiresAt);

        return $OTPCode;
    }
}
