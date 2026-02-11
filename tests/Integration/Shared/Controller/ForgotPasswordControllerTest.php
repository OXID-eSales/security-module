<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Controller;

use OxidEsales\Eshop\Application\Controller\ForgotPasswordController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;

class ForgotPasswordControllerTest extends IntegrationTestCase
{
    protected UtilsView $utilsViewMock;
    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $moduleSettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        $moduleSettings->saveIsCaptchaEnabled(true);

        $passwordSettings = ContainerFacade::get(PasswordSettingsServiceInterface::class);
        $passwordSettings->saveIsPasswordPolicyEnabled(false);

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

    public function testForgotPasswordWithValidCaptcha()
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
            });

        $subject = oxNew(ForgotPasswordController::class);
        $subject->forgotPassword();
    }

    public function testForgotPasswordWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $subject = oxNew(ForgotPasswordController::class);
        $subject->forgotPassword();
    }

    public function testForgotPasswordWithInvalidHoneyPotCaptcha()
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

        $subject = oxNew(ForgotPasswordController::class);
        $subject->forgotPassword();
    }

    public function testForgotPasswordWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('');

        $this->utilsViewMock
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_EMPTY_CAPTCHA');

        $subject = oxNew(ForgotPasswordController::class);
        $subject->forgotPassword();
    }

    public function testUpdatePasswordClearsExternalAuthFlagOnSuccess(): void
    {
        $userId = uniqid();
        $user = $this->createTestUser($userId);

        $this->assertEquals(1, (int) $user->getFieldData('oesmexternalauth'));

        $updateKey = $user->getFieldData('oxupdatekey');
        $shopId = $user->getFieldData('oxshopid');
        $uid = md5($user->getId() . $shopId . $updateKey);

        $password = uniqid();

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter', 'getRequestEscapedParameter'])
            ->getMock();

        $requestMock->method('getRequestParameter')
            ->willReturnCallback(function ($param) use ($password) {
                return match ($param) {
                    'password_new', 'password_new_confirm' => $password,
                    default => null,
                };
            });

        $requestMock->method('getRequestEscapedParameter')
            ->willReturnCallback(function ($param) use ($uid) {
                return match ($param) {
                    'uid' => $uid,
                    default => null,
                };
            });

        Registry::set(Request::class, $requestMock);

        $subject = oxNew(ForgotPasswordController::class);
        $result = $subject->updatePassword();

        $this->assertSame('forgotpwd?success=1', $result);

        $updatedUser = oxNew(User::class);
        $updatedUser->load($userId);
        $this->assertEquals(0, (int) $updatedUser->getFieldData('oesmexternalauth'));
    }

    public function testUpdatePasswordKeepsExternalAuthFlagOnFailure(): void
    {
        $userId = uniqid();
        $this->createTestUser($userId);
        $password = uniqid();

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter', 'getRequestEscapedParameter'])
            ->getMock();

        $requestMock->method('getRequestParameter')
            ->willReturnCallback(function ($param) use ($password) {
                return match ($param) {
                    'password_new', 'password_new_confirm' => $password,
                    default => null,
                };
            });

        $requestMock->method('getRequestEscapedParameter')
            ->willReturnCallback(function ($param) {
                return match ($param) {
                    'uid' => 'invalid_uid',
                    default => null,
                };
            });

        Registry::set(Request::class, $requestMock);

        $subject = oxNew(ForgotPasswordController::class);
        $result = $subject->updatePassword();

        $this->assertNotSame('forgotpwd?success=1', $result);

        $unchangedUser = oxNew(User::class);
        $unchangedUser->load($userId);
        $this->assertEquals(1, (int) $unchangedUser->getFieldData('oesmexternalauth'));
    }

    /**
     * Helper method to create the test user.
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function createTestUser(string $userId)
    {
        $user = oxNew(User::class);
        $user->setId($userId);
        $user->assign([
            'oxactive'            => 1,
            'b2bparentid'         => '',
            'b2brightdirectorder' => 1,
            'oxpassword'          => uniqid(),
            'oesmexternalauth'    => 1,
        ]);

        $user->save();
        $user->setUpdateKey();

        return $user;
    }
}
