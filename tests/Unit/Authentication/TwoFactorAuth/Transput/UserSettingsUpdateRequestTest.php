<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use Generator;
use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\UserSettingsUpdateRequest;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\UserSettingsUpdateRequestInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserSettingsUpdateRequestTest extends TestCase
{
    #[Test]
    #[DataProvider('isTwoFAEnabledDataProvider')]
    public function isTwoFAEnabled(mixed $requestValue, bool $expected): void
    {
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')
            ->with('twofa_enabled')
            ->willReturn($requestValue);

        $sut = $this->getSut(request: $requestStub);

        $this->assertSame($expected, $sut->isTwoFAEnabled());
    }

    public static function isTwoFAEnabledDataProvider(): Generator
    {
        yield 'submitted as 1' => ['requestValue' => '1', 'expected' => true];
        yield 'submitted as true' => ['requestValue' => true, 'expected' => true];
        yield 'not submitted (null)' => ['requestValue' => null, 'expected' => false];
        yield 'submitted as 0' => ['requestValue' => '0', 'expected' => false];
        yield 'submitted as array (hacked)' => ['requestValue' => ['1'], 'expected' => true];
    }

    private function getSut(RequestInterface $request): UserSettingsUpdateRequestInterface
    {
        return new UserSettingsUpdateRequest(request: $request);
    }
}
