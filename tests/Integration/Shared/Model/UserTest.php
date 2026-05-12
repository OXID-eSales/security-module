<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use Generator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
use OxidEsales\SecurityModule\Shared\Model\User as SecurityModuleUser;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;

#[AllowMockObjectsWithoutExpectations]
class UserTest extends IntegrationTestCase
{
    private const TWO_FA_USER_NAME = 'user@oxid-esales.com';
    private const TWO_FA_USER_PASSWORD = 'useruser';

    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        Registry::set(Request::class, $this->requestMock);
    }

    public function testLoginWith2FAEnabledAndUnverifiedChallengeTriggersChallenge(): void
    {
        $userId = $this->getTwoFAUserId();

        $userServiceSpy = $this->createMock(TwoFAUserServiceInterface::class);
        $userServiceSpy->method('isTwoFARequired')->with($userId)->willReturn(true);
        $userServiceSpy->method('isChallengeVerified')->with($userId)->willReturn(false);
        $userServiceSpy->expects($this->once())
            ->method('startChallengeForUser')
            ->with($userId);

        $sut = $this->getSut([
            TwoFAUserServiceInterface::class => $userServiceSpy,
        ]);
        $sut->login(self::TWO_FA_USER_NAME, self::TWO_FA_USER_PASSWORD);
    }

    public function testLoginWith2FAEnabledAndVerifiedChallengeNOTTriggeringChallengeAndLogins(): void
    {
        $userId = $this->getTwoFAUserId();

        $userServiceSpy = $this->createMock(TwoFAUserServiceInterface::class);
        $userServiceSpy->method('isTwoFARequired')->with($userId)->willReturn(true);
        $userServiceSpy->method('isChallengeVerified')->with($userId)->willReturn(true);
        $userServiceSpy->expects($this->never())->method('startChallengeForUser');

        $sut = $this->getSut([
            TwoFAUserServiceInterface::class => $userServiceSpy,
        ]);

        $result = $sut->login(self::TWO_FA_USER_NAME, self::TWO_FA_USER_PASSWORD);
        $this->assertTrue($result);
    }

    public function testLoginWithoutPasswordOnLoadedUserWith2FAEnabledAndChallengeVerifiedLogsUserIn(): void
    {
        $userId = $this->getTwoFAUserId();

        $userServiceMock = $this->createMock(TwoFAUserServiceInterface::class);
        $userServiceMock->method('isTwoFARequired')->with($userId)->willReturn(true);
        $userServiceMock->method('isChallengeVerified')->with($userId)->willReturn(true);

        $sut = $this->getSut([
            TwoFAUserServiceInterface::class => $userServiceMock,
        ]);
        $sut->load($userId);

        $result = $sut->login(self::TWO_FA_USER_NAME, null);
        $this->assertTrue($result);
    }

    public function testLoginWithoutPasswordOnLoadedUserWith2FAEnabledAndChallengeNotVerifiedTriggersChallenge(): void
    {
        $userId = $this->getTwoFAUserId();

        $userServiceSpy = $this->createMock(TwoFAUserServiceInterface::class);
        $userServiceSpy->method('isTwoFARequired')->with($userId)->willReturn(true);
        $userServiceSpy->method('isChallengeVerified')->with($userId)->willReturn(false);
        $userServiceSpy->expects($this->once())
            ->method('startChallengeForUser')
            ->with($userId);

        $sut = $this->getSut([
            TwoFAUserServiceInterface::class => $userServiceSpy,
        ]);
        $sut->load($userId);

        $sut->login(self::TWO_FA_USER_NAME, null);
    }

    #[DataProvider('invalidLoginDataProvider')]
    public function testLoginWith2FAEnabledAndBadCredentialsThrowsException(
        string $username,
        string $password
    ): void {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('ERROR_MESSAGE_USER_NOVALIDLOGIN');

        $sut = $this->getSut();
        $sut->login($username, $password);
    }

    public static function invalidLoginDataProvider(): Generator
    {
        yield 'invalid password' => [self::TWO_FA_USER_NAME, uniqid()];
        yield 'nonexistent user' => ['nonexistent@test.com', 'anypassword'];
    }

    public function testLogin2FANotRequiredDoesntTouchChallengeAndJustLogins(): void
    {
        $userId = $this->getTwoFAUserId();

        $userServiceSpy = $this->createMock(TwoFAUserServiceInterface::class);
        $userServiceSpy->method('isTwoFARequired')->with($userId)->willReturn(false);
        $userServiceSpy->expects($this->never())->method('isChallengeVerified');
        $userServiceSpy->expects($this->never())->method('startChallengeForUser');

        $sut = $this->getSut([
            TwoFAUserServiceInterface::class => $userServiceSpy,
        ]);

        $result = $sut->login(self::TWO_FA_USER_NAME, self::TWO_FA_USER_PASSWORD);
        $this->assertTrue($result);
    }

    private function getSut(array $serviceOverrides = []): SecurityModuleUser
    {
        $services = array_merge(
            [
                TwoFAUserServiceInterface::class => $this->createConfiguredStub(
                    TwoFAUserServiceInterface::class,
                    ['isTwoFARequired' => false]
                ),
            ],
            $serviceOverrides
        );

        /** @var SecurityModuleUser $sut */
        $sut = $this->getMockBuilder(SecurityModuleUser::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }

    private function getTwoFAUserId(): string
    {
        $user = oxNew(User::class);
        $user->load($user->getIdByUserName(self::TWO_FA_USER_NAME));
        return $user->getId();
    }
}
