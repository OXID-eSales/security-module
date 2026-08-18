<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email;

interface OtpMailRendererInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data): string;
}
