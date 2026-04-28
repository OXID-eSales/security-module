<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Controller;

use Generator;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Controller\ForgotPasswordController as ModuleForgotPasswordController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ForgotPasswordControllerTest extends IntegrationTestCase
{
    protected UtilsView $utilsViewMock;
    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        Registry::set(UtilsView::class, $this->utilsViewMock);
        Registry::set(Request::class, $this->requestMock);
    }

    #[Test]
    public function forgotPasswordWithValidCaptcha(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                return '';
            });

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
            });

        $subject = $this->getSut();
        $subject->forgotPassword();
    }

    #[Test]
    #[DataProvider('forgotPasswordExceptionCasesDataProvider')]
    public function forgotPasswordDisplaysErrorOnCaptchaException(string $errorCode): void
    {
        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with($errorCode);

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException($errorCode));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->forgotPassword();
    }

    public static function forgotPasswordExceptionCasesDataProvider(): Generator
    {
        yield 'invalid captcha' => ['ERROR_INVALID_CAPTCHA'];
        yield 'honey pot captcha' => ['FORM_VALIDATION_FAILED'];
        yield 'empty captcha' => ['ERROR_EMPTY_CAPTCHA'];
    }

    private function getSut(array $serviceOverrides = []): ModuleForgotPasswordController
    {
        $services = array_merge(
            [
                ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                    ModuleSettingsServiceInterface::class,
                    ['isCaptchaEnabled' => true]
                ),
                CaptchaServiceInterface::class => $this->createStub(CaptchaServiceInterface::class),
            ],
            $serviceOverrides
        );

        /** @var ModuleForgotPasswordController $sut */
        $sut = $this->getMockBuilder(ModuleForgotPasswordController::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
