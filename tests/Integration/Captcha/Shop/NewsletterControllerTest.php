<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Captcha\Shop;

use OxidEsales\Eshop\Application\Controller\NewsletterController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;

class NewsletterControllerTest extends IntegrationTestCase
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
        Registry::getSession()->setVariable('captcha', 'valid_captcha');
        Registry::getSession()->setVariable('captcha_expiration', time() + 60);
    }

    public function testSendWithValidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                return ['oxuser__oxusername' => '']; //suppress warnings from shop
            });

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
            });

        $subject = oxNew(NewsletterController::class);
        $subject->send();
    }

    public function testSendWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $subject = oxNew(NewsletterController::class);
        $subject->send();
    }

    public function testSendWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $subject = oxNew(NewsletterController::class);
        $subject->send();
    }
}
