<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Controller;

use DateTimeImmutable;
use Generator;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettings;
use OxidEsales\SecurityModule\Shared\Controller\ForgotPasswordController as ModuleForgotPasswordController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[AllowMockObjectsWithoutExpectations]
class ForgotPasswordControllerTest extends IntegrationTestCase
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

    #[Test]
    public function forgotPasswordWithValidCaptcha(): void
    {
        $this->expectNotToPerformAssertions();

        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                return '';
            });

        $this->utilsViewSpy
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
        $this->utilsViewSpy
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

    #[Test]
    public function updatePasswordConfirmsTheAddressOfAnUnconfirmedUser(): void
    {
        $userId = $this->createPendingUser();
        $this->fillNewPasswordForm($userId, 'Aa1!' . uniqid());

        $this->runWithEmailVerification(true, function () use ($userId): void {
            $this->assertSame('forgotpwd?success=1', $this->getSut()->updatePassword());
        });

        $this->assertNull($this->get(PendingVerificationRepositoryInterface::class)->findByUserId($userId));
        $this->assertNotNull($this->getVerifiedAt($userId));
        $this->assertSame($userId, $this->get(SessionInterface::class)->get('usr'));
    }

    #[Test]
    public function updatePasswordKeepsTheVerificationOpenWhenTheFeatureIsDisabled(): void
    {
        $userId = $this->createPendingUser();
        $this->fillNewPasswordForm($userId, 'Aa1!' . uniqid());

        $this->runWithEmailVerification(false, function (): void {
            $this->assertSame('forgotpwd?success=1', $this->getSut()->updatePassword());
        });

        $this->assertNotNull($this->get(PendingVerificationRepositoryInterface::class)->findByUserId($userId));
        $this->assertNull($this->getVerifiedAt($userId));
    }

    #[Test]
    public function updatePasswordConfirmsNothingWhenTheResetFails(): void
    {
        $userId = $this->createPendingUser();
        $this->fillNewPasswordForm($userId, 'Aa1!' . uniqid(), 'Aa1!' . uniqid());

        $this->runWithEmailVerification(true, function (): void {
            $this->assertNotSame('forgotpwd?success=1', $this->getSut()->updatePassword());
        });

        $this->assertNotNull($this->get(PendingVerificationRepositoryInterface::class)->findByUserId($userId));
        $this->assertNull($this->getVerifiedAt($userId));
    }

    private function createPendingUser(): string
    {
        $user = oxNew(UserModel::class);
        $user->setId(uniqid());
        $user->assign(['oxusername' => uniqid() . '@example.test', 'oxshopid' => 1, 'oxactive' => 1]);
        $user->save();

        $this->get(PendingVerificationRepositoryInterface::class)->createPendingVerification(
            userId: $user->getId(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        return $user->getId();
    }

    private function fillNewPasswordForm(string $userId, string $password, ?string $confirmation = null): void
    {
        $user = oxNew(UserModel::class);
        $user->load($userId);

        $_POST = [
            'uid' => $user->getUpdateId(),
            'password_new' => $password,
            'password_new_confirm' => $confirmation ?? $password,
        ];

        Registry::set(Request::class, new Request());
        $this->get(SessionInterface::class)->remove('usr');
    }

    private function runWithEmailVerification(bool $enabled, callable $test): void
    {
        $settingService = ContainerFactory::getInstance()->getContainer()->get(ModuleSettingServiceInterface::class);
        $originallyEnabled = $settingService->getBoolean(EmailVerificationSettings::ACTIVE, Module::MODULE_ID);
        $settingService->saveBoolean(EmailVerificationSettings::ACTIVE, $enabled, Module::MODULE_ID);

        try {
            $test();
        } finally {
            $settingService->saveBoolean(EmailVerificationSettings::ACTIVE, $originallyEnabled, Module::MODULE_ID);
        }
    }

    private function getVerifiedAt(string $userId): ?string
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('OEEMAILVERIFIED')
            ->from('oxuser')
            ->where('OXID = :userId')
            ->setParameter('userId', $userId);

        $value = $queryBuilder->execute()->fetchOne();

        return $value === false ? null : $value;
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
