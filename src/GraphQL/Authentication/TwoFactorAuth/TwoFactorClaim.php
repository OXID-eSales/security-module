<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth;

final class TwoFactorClaim
{
    public const PENDING = 'mfa_pending';

    public const EXPIRES_AT = 'mfa_exp';
}
