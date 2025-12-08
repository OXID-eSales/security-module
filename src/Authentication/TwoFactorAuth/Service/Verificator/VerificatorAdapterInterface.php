<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator;

interface VerificatorAdapterInterface
{
    public function getName(): string;

    public function validateCode(string $userId, string $inputCode): void;

    public function generate(string $userId): string;
}
