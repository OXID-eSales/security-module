<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Enum;

enum VerificationPageState: string
{
    case SENT = 'sent';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case ALREADY_VERIFIED = 'already_verified';
    case EXPIRED = 'expired';
    case INVALID = 'invalid';
}
