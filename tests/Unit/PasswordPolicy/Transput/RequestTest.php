<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Transput;

use OxidEsales\Eshop\Core\Request as ShopRequest;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    #[DataProvider('dataProviderPassword')]
    public function testGetPasswordRequest($password, $expectedValue): void
    {
        $requestMock = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestMock->method('getRequestParameter')->willReturnMap([
            ['password', null, $password]
        ]);

        $sut = new Request($requestMock);
        $this->assertSame($expectedValue, $sut->getPassword());
    }

    public static function dataProviderPassword(): iterable
    {
        yield [null, ''];
        yield ['', ''];
        yield ['MyPassword', 'MyPassword'];
        yield ['Str0ng-Passw0rd!', 'Str0ng-Passw0rd!'];
    }
}
