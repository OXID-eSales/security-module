<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\DTO;

use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;

readonly class VerificationPageDto implements VerificationPageDtoInterface
{
    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function __construct(
        private VerificationPageState $state,
        private string $token = '',
        private bool $canResend = false,
        private string $errorMessage = '',
        private int $errorArgument = 0,
    ) {
    }

    public function getState(): VerificationPageState
    {
        return $this->state;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function canResend(): bool
    {
        return $this->canResend;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getErrorArgument(): int
    {
        return $this->errorArgument;
    }
}
