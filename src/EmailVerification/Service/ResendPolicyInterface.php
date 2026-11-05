<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;

interface ResendPolicyInterface
{
    public function getCooldownRemaining(PendingVerificationDtoInterface $pendingVerification): int;
}
