<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Controller;

use LogicException;
use OxidEsales\GraphQL\Base\DataType\Login;
use OxidEsales\GraphQL\Base\DataType\LoginInterface;
use OxidEsales\GraphQL\Base\DataType\User;
use OxidEsales\GraphQL\Base\Infrastructure\Legacy;
use OxidEsales\GraphQL\Base\Service\RefreshTokenServiceInterface;
use OxidEsales\GraphQL\Base\Service\Token;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\CodeValidationException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAResendableInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception\TwoFactorChallengeException;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception\TwoFactorResendCooldownException;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service\ChallengeTokenValidatorServiceInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class TwoFactorAuthController
{
    private const BASE_REQUIRED = 'graphql-base is required for the oxapi two-factor verify mutations';

    public function __construct(
        private readonly TwoFAServiceInterface $twoFAService,
        private readonly ChallengeTokenValidatorServiceInterface $challengeValidator,
        private readonly TwoFAResendableInterface $resendService,
        private readonly ?Token $tokenService = null,
        private readonly ?Legacy $legacy = null,
        private readonly ?RefreshTokenServiceInterface $refreshTokenService = null,
    ) {
    }

    #[Mutation]
    public function verifyTwoFactorToken(string $otp): string
    {
        $user = $this->resolveVerifiedUser($otp);

        $accessToken = $this->tokenService()->createTokenForUser($user)->toString();

        $this->twoFAService->consumeChallenge((string)$user->id());

        return $accessToken;
    }

    #[Mutation]
    public function verifyTwoFactorLogin(string $otp): LoginInterface
    {
        $user = $this->resolveVerifiedUser($otp);

        $login = new Login(
            refreshToken: $this->refreshTokenService()->createRefreshTokenForUser($user),
            accessToken: $this->tokenService()->createTokenForUser($user),
        );

        $this->twoFAService->consumeChallenge((string)$user->id());

        return $login;
    }

    #[Mutation]
    public function resendTwoFactorOtp(): bool
    {
        // todo-medium: resend refreshes the OTP code + its lifetime but not the challenge JWT's
        // mfa_exp. A late resend can create an OTP that outlives the challenge Bearer, forcing a
        // re-login. Safe (no bypass), but decide whether resend should also extend the challenge
        $userId = $this->challengeValidator->validateAndGetUserId();

        try {
            $this->resendService->resend($userId);
        } catch (ResendCooldownException $exception) {
            throw new TwoFactorResendCooldownException(previous: $exception);
        }

        return true;
    }

    private function resolveVerifiedUser(string $otp): User
    {
        $userId = $this->challengeValidator->validateAndGetUserId();

        try {
            $this->twoFAService->verify($userId, $otp);
        } catch (CodeValidationException $exception) {
            throw new TwoFactorChallengeException(previous: $exception);
        }

        return new User($this->legacy()->getUserModel($userId), false);
    }

    private function tokenService(): Token
    {
        return $this->tokenService ?? throw new LogicException(self::BASE_REQUIRED);
    }

    private function legacy(): Legacy
    {
        return $this->legacy ?? throw new LogicException(self::BASE_REQUIRED);
    }

    private function refreshTokenService(): RefreshTokenServiceInterface
    {
        return $this->refreshTokenService ?? throw new LogicException(self::BASE_REQUIRED);
    }
}
