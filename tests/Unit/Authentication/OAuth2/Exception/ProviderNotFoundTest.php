<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Exception;

use Codeception\PHPUnit\TestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;

class ProviderNotFoundTest extends TestCase
{
    public function testException(): void
    {
        $exception = new ProviderNotFoundException();

        $this->assertInstanceOf(ProviderNotFoundException::class, $exception);
    }
}
