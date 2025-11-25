<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

use DateTimeInterface;

interface UserDTOInterface
{
    public function getCode(): ?string;

    public function getAttempts(): ?int;

    public function getExpiresAt(): ?DateTimeInterface;
}
