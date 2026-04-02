<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Factory\OtpNotifierFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpChallengeStateServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeGeneratorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeValidatorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;

class OtpFacade implements TwoFAServiceInterface
{
    public function __construct(
        private OtpChallengeStateServiceInterface $stateService,
        private OtpCodeValidatorServiceInterface $codeValidator,
        private OtpCodeGeneratorServiceInterface $codeGenerator,
        private OtpNotifierFactoryInterface $notifierFactory,
    ) {
    }

    public function isVerified(string $userId): bool
    {
        $state = $this->stateService->getChallengeState($userId);

        if ($state === null || $state->getVerifiedAt() === null) {
            return false;
        }

        return $state->getExpiresAt() > new \DateTimeImmutable();
    }

    public function triggerChallenge(string $userId): void
    {
        $code = $this->codeGenerator->generateCode();

        // todo-critical: check if we can trigger the notification

        $this->stateService->createChallengeState($userId, $code);
        $this->notifierFactory->create($userId)->notify($userId, $code);
    }

    public function invalidateChallenge(string $userId): void
    {
        $this->stateService->deleteChallengeState($userId);
    }

    public function verify(string $userId, #[\SensitiveParameter] string $code): void
    {
        $this->codeValidator->validateCode($userId, $code);
        $this->stateService->markVerified($userId);
    }

    public function resend(string $userId): void
    {
        // todo-critical: implement
    }
}
