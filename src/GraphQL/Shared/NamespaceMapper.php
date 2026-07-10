<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Shared;

final class NamespaceMapper
{
    private const SPACE = '\\OxidEsales\\SecurityModule\\GraphQL\\';

    public function getControllerNamespaceMapping(): array
    {
        return [
            self::SPACE . 'Authentication\\TwoFactorAuth\\Controller'
                => __DIR__ . '/../Authentication/TwoFactorAuth/Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        return [
            self::SPACE . 'Authentication\\TwoFactorAuth\\DataType'
                => __DIR__ . '/../Authentication/TwoFactorAuth/DataType/',
        ];
    }
}
