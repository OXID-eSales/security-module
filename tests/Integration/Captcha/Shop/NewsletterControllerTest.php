<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Captcha\Shop;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Captcha\Shop\NewsletterController as ModuleNewsletterController;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class NewsletterControllerTest extends IntegrationTestCase
{
    protected UtilsView $utilsViewSpy;
    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->utilsViewSpy = $this->createMock(UtilsView::class);

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        Registry::set(UtilsView::class, $this->utilsViewSpy);
        Registry::set(Request::class, $this->requestMock);
    }

    public function testSendWithValidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'lastname_confirm') {
                    return '';
                }
                return ['oxuser__oxusername' => '']; //suppress warnings from shop
            });

        $this->utilsViewSpy
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
            });

        $subject = $this->getSut();
        $subject->send();
    }

    public function testSendWithInvalidCaptcha()
    {
        $this->utilsViewSpy
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_INVALID_CAPTCHA'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->send();
    }

    public function testSendWithEmptyCaptcha()
    {
        $this->utilsViewSpy
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_EMPTY_CAPTCHA'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->send();
    }

    public function testSendWithInvalidHoneyPotCaptcha()
    {
        $this->utilsViewSpy
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('FORM_VALIDATION_FAILED'));

        $subject = $this->getSut([CaptchaServiceInterface::class => $captchaService]);
        $subject->send();
    }

    private function getSut(array $serviceOverrides = []): ModuleNewsletterController
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

        /** @var ModuleNewsletterController $sut */
        $sut = $this->getMockBuilder(ModuleNewsletterController::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
