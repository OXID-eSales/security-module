<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Factory;

use OxidEsales\SecurityModule\EmailVerification\DTO\VerificationPageDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;

interface VerificationPageDtoFactoryInterface
{
    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function create(
        VerificationPageState $state,
        string $token = '',
        bool $canResend = false,
        string $errorMessage = '',
        int $errorArgument = 0,
    ): VerificationPageDtoInterface;
}
