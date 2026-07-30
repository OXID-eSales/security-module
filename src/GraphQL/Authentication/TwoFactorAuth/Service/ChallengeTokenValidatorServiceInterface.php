<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service;

interface ChallengeTokenValidatorServiceInterface
{
    public function validateAndGetUserId(): string;
}
