<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Component;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\Shared\Component\UserComponent as SecurityModuleUserComponent;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;

#[AllowMockObjectsWithoutExpectations]
class UserComponentEmailVerificationTest extends IntegrationTestCase
{
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
