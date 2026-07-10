<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth;

/**
 * Wire-contract keys for the oxapi 2FA challenge JWT claims. Shared by the writer
 * (BeforeTokenCreationSubscriber) and the reader (ChallengeTokenValidatorService) so the two can
 * never drift: a one-sided rename would otherwise make every challenge silently fail validation.
 */
final class TwoFactorClaim
{
    /** Marks a JWT as a 2FA challenge token that the verify mutations exchange for a full token. */
    public const PENDING = 'mfa_pending';

    /** Unix timestamp after which the challenge token is no longer accepted by the verify flow. */
    public const EXPIRES_AT = 'mfa_exp';
}
