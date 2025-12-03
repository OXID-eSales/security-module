<?php

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

readonly class OTPGenerator implements OTPGeneratorInterface
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRATION = 300; // 5 minutes

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function generateCode(UserModel $user): User
    {
        $otp = str_pad(random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
        $expiresAt = time() + self::OTP_EXPIRATION;

        $this->userRepository->addOTPtoUser($user->getId(), $otp, $expiresAt);

        return new User($otp, 0, $expiresAt);
    }
}
