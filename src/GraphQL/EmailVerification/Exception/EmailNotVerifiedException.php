<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\EmailVerification\Exception;

use OxidEsales\GraphQL\Base\Exception\Error;
use OxidEsales\GraphQL\Base\Exception\ErrorCategories;
use Throwable;

final class EmailNotVerifiedException extends Error
{
    private const MESSAGE = 'Please confirm your email address before logging in';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            message: self::MESSAGE,
            previous: $previous,
            category: ErrorCategories::PERMISSIONERRORS,
        );
    }
}
