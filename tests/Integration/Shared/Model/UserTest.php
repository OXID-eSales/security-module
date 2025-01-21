<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;

class UserTest extends IntegrationTestCase
{
    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        Registry::set(Request::class, $this->requestMock);
        Registry::getSession()->setVariable('captcha', 'valid_captcha');
    }

    public function testCheckValuesWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'invalid_captcha';
                }
                return null; //suppress warnings from shop
            });

        $this->expectException(InputException::class);
        $this->expectExceptionMessage("ERROR_INVALID_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->checkValues('', '', '', [], []);
    }

    public function testCheckValuesWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return '';
                }
                return null; //suppress warnings from shop
            });

        $this->expectException(InputException::class);
        $this->expectExceptionMessage("ERROR_EMPTY_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->checkValues('', '', '', [], []);
    }

    public function testLoginWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_INVALID_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->login('', '');
    }

    public function testLoginWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('');

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_EMPTY_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->login('', '');
    }
}
