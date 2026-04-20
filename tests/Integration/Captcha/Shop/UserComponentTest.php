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
            ->expects($this->atLeastOnce())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $subject = oxNew(UserComponent::class);
        $result = $subject->createUser();

        $this->assertFalse($result);
    }
}
