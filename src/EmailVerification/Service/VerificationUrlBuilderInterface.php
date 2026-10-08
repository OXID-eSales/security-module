<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

interface VerificationUrlBuilderInterface
{
    public function buildVerificationLink(#[\SensitiveParameter] string $token): string;

    public function buildPendingPageUrl(): string;
}
