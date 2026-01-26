<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator\OTPGeneratorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Validator\OTPValidatorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;

class OTPVerificator implements VerificatorAdapterInterface
{
    public function __construct(
        private OTPGeneratorInterface $otpGenerator,
        private OTPValidatorInterface $otpValidator,
        private UserRepositoryInterface $userRepository,
        private Config $config,
    ) {
    }

    public function getName(): string
    {
        return 'otp';
    }

    public function validateCode(string $userId, string $inputCode): void
    {
        $otpData = $this->userRepository->getUserOTPData($userId);

        $this->otpValidator->checkLoginAttempts($otpData->getAttempts());
        $this->otpValidator->checkExpirationTime($otpData->getExpiresAt());

        try {
            $this->otpValidator->validateCode($otpData->getCode(), $inputCode);
        } catch (InvalidCodeException $e) {
            $this->userRepository->updateAttempts($userId, $otpData->getAttempts() + 1);
            throw $e;
        }

        $this->userRepository->resetCodeFields($userId);
    }

    public function generate(string $userId): string
    {
        return $this->otpGenerator->generateCode($userId);
    }

    public function getVerificationUrl(): string
    {
        return $this->config->getShopHomeUrl() . 'cl=twofactorauth';
    }
}
