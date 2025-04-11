<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Captcha\HoneyPot\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service\HoneyPotCaptchaService;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HoneyPotCaptchaServiceTest extends TestCase
{
    #[DataProvider('enabledDataProvider')]
    public function testValidationWithDisabledSetting(bool $settingValue, bool $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'isHoneyPotCaptchaEnabled' => $settingValue
            ])
        );

        $this->assertSame($expectedValue, $sut->isEnabled());
    }

    public static function enabledDataProvider(): \Generator
    {
        yield 'setting enabled' => [
            'settingValue' => true,
            'expectedValue' => true,
        ];

        yield 'setting disabled' => [
            'settingValue' => false,
            'expectedValue' => false,
        ];
    }

    public function testValidationThrowsException(): void
    {
        $value = uniqid();

        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestParameter'])
            ->getMock();
        $request
            ->method('getRequestParameter')
            ->with(HoneyPotCaptchaService::CAPTCHA_REQUEST_PARAMETER)
            ->willReturn($value);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $sut = $this->getSut(
            logger: $logger
        );

        $this->expectException(CaptchaValidateException::class);
        $sut->validate($request);
    }

    #[DataProvider('captchaDataProvider')]
    public function testValidationDoesNotThrowsException($requestValue): void
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestParameter'])
            ->getMock();
        $request
            ->method('getRequestParameter')
            ->with(HoneyPotCaptchaService::CAPTCHA_REQUEST_PARAMETER)
            ->willReturn($requestValue);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('debug');

        $sut = $this->getSut(
            logger: $logger
        );

        $sut->validate($request);
    }

    public static function captchaDataProvider(): \Generator
    {
        yield 'empty value' => [
            'requestValue' => ''
        ];

        yield 'null value' => [
            'requestValue' => null
        ];

        yield 'wrong value' => [
            'requestValue' => false
        ];
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSetting = null,
        LoggerInterface $logger = null
    ): HoneyPotCaptchaService {
        return new HoneyPotCaptchaService(
            moduleSetting: $moduleSetting ?? $this->createStub(ModuleSettingsServiceInterface::class),
            logger: $logger ?? $this->createStub(LoggerInterface::class)
        );
    }
}
