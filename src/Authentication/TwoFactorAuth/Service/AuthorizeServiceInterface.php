<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface AuthorizeServiceInterface
{
    public function validate(#[\SensitiveParameter] string $inputCode): void;

    public function generate(): void;

    public function getVerificationUrl(): string;

    public function getRemainingAttempts(): int;
}
