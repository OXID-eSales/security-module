<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\DTO;

use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;

interface VerificationPageDtoInterface
{
    public function getState(): VerificationPageState;

    public function getToken(): string;

    public function canResend(): bool;

    public function getErrorMessage(): string;

    public function getErrorArgument(): int;
}
