<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Captcha\Shop;

use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class UserComponentTest extends IntegrationTestCase
{
    protected UtilsView $utilsViewMock;
    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $moduleSettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        $moduleSettings->saveIsCaptchaEnabled(true);

        $this->utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter', 'getRequestEscapedParameter'])
            ->getMock();

        Registry::set(UtilsView::class, $this->utilsViewMock);
        Registry::set(Request::class, $this->requestMock);
        Registry::getSession()->setVariable('captcha', 'valid_captcha');
        Registry::getSession()->setVariable('captcha_expiration', time() + 60);
    }

    public function tearDown(): void
    {
        $moduleSettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        $moduleSettings->saveIsCaptchaEnabled(false);

        parent::tearDown();
    }

    public function testLoginWithInvalidCaptchaReturnsUserAndShowsError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $subject = oxNew(UserComponent::class);
        $result = $subject->login();

        $this->assertSame('user', $result);
    }

    public function testLoginWithEmptyCaptchaReturnsUserAndShowsError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $subject = oxNew(UserComponent::class);
        $result = $subject->login();

        $this->assertSame('user', $result);
    }

    public function testLoginWithInvalidHoneyPotReturnsUserAndShowsError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                if ($param === 'lastname_confirm') {
                    return 'some-text';
                }
                return '';
            });

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $subject = oxNew(UserComponent::class);
        $result = $subject->login();

        $this->assertSame('user', $result);
    }

    public function testCreateUserWithInvalidCaptchaReturnsFalseAndShowsError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $subject = oxNew(UserComponent::class);
        $result = $subject->createUser();

        $this->assertFalse($result);
    }

    public function testLoginWithValidCaptchaDoesNotShowCaptchaError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                return '';
            });

        $this->requestMock
            ->method('getRequestEscapedParameter')
            ->willReturn('');

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $subject = oxNew(UserComponent::class);
        $subject->login();
    }

    public function testCreateUserWithValidCaptchaDoesNotShowCaptchaError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                return '';
            });

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $subject = oxNew(UserComponent::class);
        $subject->createUser();
    }

    public function testCreateUserWithInvalidHoneyPotReturnsFalseAndShowsError(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                if ($param === 'lastname_confirm') {
                    return 'some-text';
                }
                return '';
            });

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $subject = oxNew(UserComponent::class);
        $result = $subject->createUser();

        $this->assertFalse($result);
    }

    public function testLoginWithCaptchaDisabledDoesNotValidateCaptcha(): void
    {
        $moduleSettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        $moduleSettings->saveIsCaptchaEnabled(false);

        $this->requestMock
            ->method('getRequestParameter')
            ->willReturn('');

        $this->requestMock
            ->method('getRequestEscapedParameter')
            ->willReturn('');

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $subject = oxNew(UserComponent::class);
        $subject->login();
    }

    public function testCreateUserWithCaptchaDisabledDoesNotValidateCaptcha(): void
    {
        $moduleSettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        $moduleSettings->saveIsCaptchaEnabled(false);

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $subject = oxNew(UserComponent::class);
        $subject->createUser();
    }
}
