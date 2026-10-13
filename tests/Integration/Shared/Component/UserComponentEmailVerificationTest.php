<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Component;

use DateTimeImmutable;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettings;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\Shared\Component\UserComponent as SecurityModuleUserComponent;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;

#[AllowMockObjectsWithoutExpectations]
class UserComponentEmailVerificationTest extends IntegrationTestCase
{
    private const FIXTURE_USER_NAME = 'user@oxid-esales.com';
    private const FIXTURE_USER_PASSWORD = 'useruser';

    #[Test]
    public function createUserStopsAtCheckYourEmailWhenEmailVerificationIsRequired(): void
    {
        $userName = $this->fillRegistrationForm();
        $facadeSpy = $this->createMock(UserEmailVerificationFacadeInterface::class);
        $facadeSpy->method('isEmailVerificationRequired')->willReturn(true);
        $facadeSpy->expects($this->once())
            ->method('rememberPendingUser')
            ->with($this->callback(fn(string $userId): bool => $userId === $this->getUserId($userName)));

        $sut = $this->getSut([UserEmailVerificationFacadeInterface::class => $facadeSpy]);

        $this->assertSame('oesm_email_verification?state=sent', $sut->createUser());
        $this->assertEmpty($this->get(SessionInterface::class)->get('usr'));
    }

    #[Test]
    public function createUserKeepsTheShopFlowWhenEmailVerificationIsNotRequired(): void
    {
        $userName = $this->fillRegistrationForm();
        $facadeSpy = $this->createMock(UserEmailVerificationFacadeInterface::class);
        $facadeSpy->method('isEmailVerificationRequired')->willReturn(false);
        $facadeSpy->expects($this->never())->method('rememberPendingUser');

        $sut = $this->getSut([UserEmailVerificationFacadeInterface::class => $facadeSpy]);

        $this->assertNotSame('oesm_email_verification?state=sent', $sut->createUser());
        $this->assertSame($this->getUserId($userName), $this->get(SessionInterface::class)->get('usr'));
    }

    #[Test]
    public function registerUserStopsAtCheckYourEmailWhenEmailVerificationIsRequired(): void
    {
        $this->fillRegistrationForm();
        $facadeStub = $this->createConfiguredStub(UserEmailVerificationFacadeInterface::class, [
            'isEmailVerificationRequired' => true,
        ]);

        $sut = $this->getSut([UserEmailVerificationFacadeInterface::class => $facadeStub]);

        $this->assertSame('oesm_email_verification?state=sent', $sut->registerUser());
    }

    #[Test]
    public function loginRedirectsToCheckYourEmailWhenEmailVerificationIsRequired(): void
    {
        $userId = $this->getUserId(self::FIXTURE_USER_NAME);
        $this->get(PendingVerificationRepositoryInterface::class)->createPendingVerification(
            userId: $userId,
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $_POST = ['lgn_usr' => self::FIXTURE_USER_NAME, 'lgn_pwd' => self::FIXTURE_USER_PASSWORD];
        $this->get(SessionInterface::class)->remove('usr');

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($this->logicalAnd(
                $this->stringContains('cl=oesm_email_verification'),
                $this->stringEndsWith('&state=pending')
            ));

        $originallyEnabled = $this->getModuleSettingService()
            ->getBoolean(EmailVerificationSettings::ACTIVE, Module::MODULE_ID);
        $this->getModuleSettingService()->saveBoolean(EmailVerificationSettings::ACTIVE, true, Module::MODULE_ID);

        try {
            $sut = $this->getSut([Utils::class => $utilsSpy]);

            $this->assertSame('user', $sut->login());
            $this->assertSame($userId, $this->get(PendingUserSessionInterface::class)->getPendingUserId());
            $this->assertEmpty($this->get(SessionInterface::class)->get('usr'));
        } finally {
            $this->getModuleSettingService()
                ->saveBoolean(EmailVerificationSettings::ACTIVE, $originallyEnabled, Module::MODULE_ID);
        }
    }

    private function fillRegistrationForm(): string
    {
        $userName = uniqid() . '@example.test';
        $password = 'Aa1!' . uniqid();
        $csrfToken = uniqid();

        $_POST = [
            'stoken' => $csrfToken,
            'option' => 3,
            'lgn_usr' => $userName,
            'lgn_pwd' => $password,
            'lgn_pwd2' => $password,
            'invadr' => [
                'oxuser__oxfname' => uniqid(),
                'oxuser__oxlname' => uniqid(),
                'oxuser__oxstreet' => uniqid(),
                'oxuser__oxstreetnr' => (string) rand(1, 999),
                'oxuser__oxzip' => (string) rand(10000, 99999),
                'oxuser__oxcity' => uniqid(),
                'oxuser__oxcountryid' => 'a7c40f631fc920687.20179984',
            ],
        ];

        $session = $this->get(SessionInterface::class);
        $session->set('sess_stoken', $csrfToken);
        $session->remove('usr');

        return $userName;
    }

    private function getModuleSettingService(): ModuleSettingServiceInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(ModuleSettingServiceInterface::class);
    }

    private function getUserId(string $userName): string
    {
        return (string) oxNew(UserModel::class)->getIdByUserName($userName);
    }

    /**
     * @param array<string, object> $serviceOverrides
     */
    private function getSut(array $serviceOverrides = []): SecurityModuleUserComponent
    {
        $services = array_merge(
            [
                ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                    ModuleSettingsServiceInterface::class,
                    ['isCaptchaEnabled' => false, 'isHoneyPotCaptchaEnabled' => false]
                ),
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
        $sut->setParent(oxNew(FrontendController::class));

        return $sut;
    }
}
