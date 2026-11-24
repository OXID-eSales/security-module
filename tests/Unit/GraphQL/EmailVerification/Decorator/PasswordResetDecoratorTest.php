<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\EmailVerification\Decorator;

use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Storefront\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Storefront\Customer\Service\PasswordInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\GraphQL\EmailVerification\Decorator\PasswordResetDecorator;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PasswordResetDecoratorTest extends TestCase
{
    #[Test]
    public function resetPasswordConfirmsTheEmailOfTheResetUser(): void
    {
        $updateHash = uniqid();
        $newPassword = uniqid();
        $repeatPassword = uniqid();
        $userId = uniqid();

        $userModelMock = $this->createConfiguredMock(
            EshopUserModel::class,
            ['isLoaded' => true, 'getId' => $userId]
        );
        $userModelMock->expects($this->once())->method('loadUserByUpdateId')->with($updateHash);

        $originalServiceMock = $this->createMock(PasswordInterface::class);
        $originalServiceMock->expects($this->once())
            ->method('resetPasswordByUpdateHash')
            ->with($updateHash, $newPassword, $repeatPassword)
            ->willReturn(true);

        $verificationFacadeMock = $this->createMock(UserEmailVerificationFacadeInterface::class);
        $verificationFacadeMock->expects($this->once())
            ->method('confirmEmailAfterPasswordReset')
            ->with($userId);

        $sut = $this->getSut(
            originalService: $originalServiceMock,
            userModelFactory: $this->createConfiguredStub(
                UserModelFactoryInterface::class,
                ['create' => $userModelMock]
            ),
            verificationFacade: $verificationFacadeMock,
        );

        $this->assertTrue($sut->resetPasswordByUpdateHash($updateHash, $newPassword, $repeatPassword));
    }

    #[Test]
    public function resetPasswordConfirmsNothingWhenTheResetFails(): void
    {
        $originalServiceStub = $this->createConfiguredStub(
            PasswordInterface::class,
            ['resetPasswordByUpdateHash' => false]
        );

        $verificationFacadeMock = $this->createMock(UserEmailVerificationFacadeInterface::class);
        $verificationFacadeMock->expects($this->never())->method('confirmEmailAfterPasswordReset');

        $sut = $this->getSut(
            originalService: $originalServiceStub,
            userModelFactory: $this->createConfiguredStub(
                UserModelFactoryInterface::class,
                ['create' => $this->createConfiguredStub(
                    EshopUserModel::class,
                    ['isLoaded' => true, 'getId' => uniqid()]
                )]
            ),
            verificationFacade: $verificationFacadeMock,
        );

        $this->assertFalse($sut->resetPasswordByUpdateHash(uniqid(), uniqid(), uniqid()));
    }

    #[Test]
    public function resetPasswordConfirmsNothingWhenNoUserMatchesTheHash(): void
    {
        $originalServiceStub = $this->createConfiguredStub(
            PasswordInterface::class,
            ['resetPasswordByUpdateHash' => true]
        );

        $verificationFacadeMock = $this->createMock(UserEmailVerificationFacadeInterface::class);
        $verificationFacadeMock->expects($this->never())->method('confirmEmailAfterPasswordReset');

        $sut = $this->getSut(
            originalService: $originalServiceStub,
            userModelFactory: $this->createConfiguredStub(
                UserModelFactoryInterface::class,
                ['create' => $this->createConfiguredStub(EshopUserModel::class, ['isLoaded' => false])]
            ),
            verificationFacade: $verificationFacadeMock,
        );

        $this->assertTrue($sut->resetPasswordByUpdateHash(uniqid(), uniqid(), uniqid()));
    }

    #[Test]
    public function changeDelegatesToOriginalService(): void
    {
        $old = uniqid();
        $new = uniqid();
        $customer = new CustomerDataType($this->createStub(EshopUserModel::class));

        $originalServiceMock = $this->createMock(PasswordInterface::class);
        $originalServiceMock->expects($this->once())
            ->method('change')
            ->with($old, $new)
            ->willReturn($customer);

        $sut = $this->getSut(originalService: $originalServiceMock);

        $this->assertSame($customer, $sut->change($old, $new));
    }

    #[Test]
    public function sendPasswordForgotEmailDelegatesToOriginalService(): void
    {
        $email = uniqid() . '@example.test';

        $originalServiceMock = $this->createMock(PasswordInterface::class);
        $originalServiceMock->expects($this->once())
            ->method('sendPasswordForgotEmail')
            ->with($email)
            ->willReturn(true);

        $sut = $this->getSut(originalService: $originalServiceMock);

        $this->assertTrue($sut->sendPasswordForgotEmail($email));
    }

    private function getSut(
        ?PasswordInterface $originalService = null,
        ?UserModelFactoryInterface $userModelFactory = null,
        ?UserEmailVerificationFacadeInterface $verificationFacade = null,
    ): PasswordResetDecorator {
        return new PasswordResetDecorator(
            originalService: $originalService ?? $this->createStub(PasswordInterface::class),
            userModelFactory: $userModelFactory ?? $this->createStub(UserModelFactoryInterface::class),
            verificationFacade: $verificationFacade ?? $this->createStub(UserEmailVerificationFacadeInterface::class),
        );
    }
}
