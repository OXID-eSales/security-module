<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Controller;

use LogicException;
use OxidEsales\GraphQL\Base\Service\Token;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;

final class TwoFactorAuthSettingsController
{
    private const BASE_REQUIRED = 'graphql-base is required for the oxapi two-factor settings mutations';

    public function __construct(
        private readonly TwoFAUserSettingsInterface $userSettings,
        private readonly ?Token $tokenService = null,
    ) {
    }

    /**
     * Enables or disables the two-factor authentication preference for the authenticated user.
     * Mirrors the storefront AccountSecurityController::saveTwoFactorAuth() flow; the remember-me
     * cookie reset done there is storefront-only and not applicable to the headless API.
     */
    #[Mutation]
    #[Logged]
    public function setTwoFactorAuth(bool $enabled): bool
    {
        $userId = (string)$this->tokenService()->getTokenClaim(Token::CLAIM_USERID);

        $this->userSettings->setEnabledForUser($userId, $enabled);

        return $this->userSettings->isEnabledForUser($userId);
    }

    private function tokenService(): Token
    {
        return $this->tokenService ?? throw new LogicException(self::BASE_REQUIRED);
    }
}
