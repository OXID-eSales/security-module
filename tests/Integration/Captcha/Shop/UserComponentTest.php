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
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettings;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Captcha\Shop\UserComponent as SecurityModuleUserComponent;
use OxidEsales\SecurityModule\Core\Module;

class UserComponentTest extends IntegrationTestCase
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
            ->onlyMethods(['getRequestParameter', 'getRequestEscapedParameter'])
            ->getMock();

        Registry::set(UtilsView::class, $this->utilsViewMock);
        Registry::set(Request::class, $this->requestMock);
    }

    public function testLoginWithInvalidCaptchaReturnsUserAndShowsError(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_INVALID_CAPTCHA'));

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $result = $this->getSut([CaptchaServiceInterface::class => $captchaService])->login();

        $this->assertSame('user', $result);
    }

    public function testLoginWithEmptyCaptchaReturnsUserAndShowsError(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_EMPTY_CAPTCHA'));

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $result = $this->getSut([CaptchaServiceInterface::class => $captchaService])->login();

        $this->assertSame('user', $result);
    }

    public function testLoginWithInvalidHoneyPotReturnsUserAndShowsError(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('FORM_VALIDATION_FAILED'));

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $result = $this->getSut([CaptchaServiceInterface::class => $captchaService])->login();

        $this->assertSame('user', $result);
    }

    public function testCreateUserWithInvalidCaptchaReturnsFalseAndShowsError(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('ERROR_INVALID_CAPTCHA'));

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $result = $this->getSut([CaptchaServiceInterface::class => $captchaService])->createUser();

        $this->assertFalse($result);
    }

    public function testCreateUserWithInvalidHoneyPotReturnsFalseAndShowsError(): void
    {
        $captchaService = $this->createMock(CaptchaServiceInterface::class);
        $captchaService->method('validate')->willThrowException(new StandardException('FORM_VALIDATION_FAILED'));

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('FORM_VALIDATION_FAILED');

        $result = $this->getSut([CaptchaServiceInterface::class => $captchaService])->createUser();

        $this->assertFalse($result);
    }

    public function testLoginWithValidCaptchaDoesNotShowCaptchaError(): void
    {
        $this->requestMock->method('getRequestParameter')->willReturn('');
        $this->requestMock->method('getRequestEscapedParameter')->willReturn('');

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $this->getSut()->login();
    }

    public function testCreateUserWithValidCaptchaDoesNotShowCaptchaError(): void
    {
        $this->requestMock->method('getRequestParameter')->willReturn('');

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $this->getSut()->createUser();
    }

    public function testLoginWithCaptchaDisabledDoesNotValidateCaptcha(): void
    {
        $this->requestMock->method('getRequestParameter')->willReturn('');
        $this->requestMock->method('getRequestEscapedParameter')->willReturn('');

        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $this->getSut([
            ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['isCaptchaEnabled' => false, 'isHoneyPotCaptchaEnabled' => false]
            ),
        ])->login();
    }

    public function testCreateUserWithCaptchaDisabledDoesNotValidateCaptcha(): void
    {
        $this->utilsViewMock
            ->expects($this->any())
            ->method('addErrorToDisplay')
            ->willReturnCallback(function ($message) {
                $this->assertNotEquals('ERROR_INVALID_CAPTCHA', $message);
                $this->assertNotEquals('ERROR_EMPTY_CAPTCHA', $message);
                $this->assertNotEquals('FORM_VALIDATION_FAILED', $message);
            });

        $this->getSut([
            ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['isCaptchaEnabled' => false, 'isHoneyPotCaptchaEnabled' => false]
            ),
        ])->createUser();
    }

    public function testRedirectsToVerificationUrlOnTwoFactorChallenge(): void
    {
        $plainPassword = 'pw-' . uniqid();
        $username = $this->insertTwoFactorEnabledUser($plainPassword);
        $this->enableTwoFactorAuthAtShopLevel();

        $this->requestMock->method('getRequestEscapedParameter')->willReturnCallback(
            fn(string $name) => $name === 'lgn_usr' ? $username : ''
        );
        $this->requestMock->method('getRequestParameter')->willReturnCallback(
            fn(string $name) => $name === 'lgn_pwd' ? $plainPassword : ''
        );

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())->method('redirect');

        $sut = $this->getSut([
            Utils::class => $utilsMock,
            ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['isCaptchaEnabled' => false, 'isHoneyPotCaptchaEnabled' => false]
            ),
        ]);

        $this->assertSame('user', $sut->login());
    }

    private function insertTwoFactorEnabledUser(string $plainPassword): string
    {
        $username = 'test-' . uniqid() . '@example.com';
        $userId = uniqid('uid');

        ContainerFactory::getInstance()
            ->getContainer()
            ->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection()
            ->executeStatement(
                'INSERT INTO oxuser'
                . ' (OXID, OXACTIVE, OXSHOPID, OXRIGHTS, OXUSERNAME, OXPASSWORD, OXPASSSALT, OE2FAENABLED)'
                . ' VALUES (?, 1, 1, "user", ?, ?, "", 1)',
                [$userId, $username, password_hash($plainPassword, PASSWORD_BCRYPT)]
            );

        return $username;
    }

    private function enableTwoFactorAuthAtShopLevel(): void
    {
        ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingServiceInterface::class)
            ->saveBoolean(TwoFAShopSettings::ACTIVE, true, Module::MODULE_ID);
    }

    private function getSut(array $serviceOverrides = []): SecurityModuleUserComponent
    {
        $services = array_merge(
            [
                ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                    ModuleSettingsServiceInterface::class,
                    ['isCaptchaEnabled' => true, 'isHoneyPotCaptchaEnabled' => false]
                ),
                CaptchaServiceInterface::class => $this->createStub(CaptchaServiceInterface::class),
            ],
            $serviceOverrides
        );

        /** @var SecurityModuleUserComponent $sut */
        $sut = $this->getMockBuilder(SecurityModuleUserComponent::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
