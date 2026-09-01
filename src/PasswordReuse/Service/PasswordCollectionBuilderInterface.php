<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\Service;

interface PasswordCollectionBuilderInterface
{
    /**
     * @return list<string>
     */
    public function build(string $userId, string $currentHash): array;
}
