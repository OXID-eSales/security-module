<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\DTO\VerificationPageDtoInterface;

interface VerificationPageServiceInterface
{
    public function showPage(bool $sentRequested): VerificationPageDtoInterface;

    public function verifyEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface;

    public function resendEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface;

    public function rejectResendEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface;
}
