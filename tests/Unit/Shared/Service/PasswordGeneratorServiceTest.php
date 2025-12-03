<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Shared\Service;

use OxidEsales\SecurityModule\Shared\Service\PasswordGeneratorService;
use PHPUnit\Framework\TestCase;

class PasswordGeneratorServiceTest extends TestCase
{
    public function testGeneratePasswordForOAuthUser(): void
    {
        $sut = new PasswordGeneratorService();

        $result = $sut->generatePasswordForOAuthUser();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result);
    }
}
