<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception;

use OxidEsales\GraphQL\Base\Exception\Error;
use OxidEsales\GraphQL\Base\Exception\ErrorCategories;
use Throwable;

final class TwoFactorChallengeException extends Error
{
    private const MESSAGE = 'Invalid or expired two-factor code';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            message: self::MESSAGE,
            previous: $previous,
            category: ErrorCategories::REQUESTERROR,
        );
    }
}
