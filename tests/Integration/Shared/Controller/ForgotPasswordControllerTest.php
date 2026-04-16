<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Controller;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Controller\ForgotPasswordController as ModuleForgotPasswordController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;

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

    public function testForgotPasswordWithValidCaptcha()
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

    public function testForgotPasswordWithInvalidCaptcha()
    {
        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_INVALID_CAPTCHA'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->forgotPassword();
    }

    public function testForgotPasswordWithInvalidHoneyPotCaptcha()
    {
        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('FORM_VALIDATION_FAILED'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->forgotPassword();
    }

    public function testForgotPasswordWithEmptyCaptcha()
    {
        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_EMPTY_CAPTCHA'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->forgotPassword();
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
