<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Facade;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailNotVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacade;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailVerificationServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationUrlBuilderInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserEmailVerificationFacadeTest extends TestCase
{
    #[Test]
    public function isEmailVerificationRequired(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $pendingRepositoryMock = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositoryMock->method('findByUserId')
            ->with($userId = uniqid())
            ->willReturn($this->createStub(PendingVerificationDtoInterface::class));

        $sut = $this->getSut(settings: $settingsStub, pendingRepository: $pendingRepositoryMock);

        $this->assertTrue($sut->isEmailVerificationRequired($userId));
    }

    #[Test]
    public function isEmailVerificationRequiredReturnsFalseWithoutAnOpenVerification(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByUserId' => null,
        ]);

        $sut = $this->getSut(settings: $settingsStub, pendingRepository: $pendingRepositoryStub);

        $this->assertFalse($sut->isEmailVerificationRequired(uniqid()));
    }

    #[Test]
    public function isEmailVerificationRequiredReturnsFalseWhenTheFeatureIsDisabled(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => false,
        ]);
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByUserId' => $this->createStub(PendingVerificationDtoInterface::class),
        ]);

        $sut = $this->getSut(settings: $settingsStub, pendingRepository: $pendingRepositoryStub);

        $this->assertFalse($sut->isEmailVerificationRequired(uniqid()));
    }

    #[Test]
    public function initializeEmailVerification(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $userStub = $this->createConfiguredStub(User::class, ['getId' => $userId = uniqid()]);
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->once())->method('startEmailVerification')->with($userId);

        $sut = $this->getSut(settings: $settingsStub, verificationService: $verificationServiceSpy);

        $this->assertTrue($sut->initializeEmailVerification($userStub));
    }

    #[Test]
    public function initializeEmailVerificationReturnsFalseWhenTheFeatureIsDisabled(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => false,
        ]);
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->never())->method('startEmailVerification');

        $sut = $this->getSut(settings: $settingsStub, verificationService: $verificationServiceSpy);

        $this->assertFalse($sut->initializeEmailVerification($this->createStub(User::class)));
    }

    #[Test]
    public function initializeEmailVerificationIgnoresAFailedMail(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $verificationServiceStub = $this->createStub(EmailVerificationServiceInterface::class);
        $verificationServiceStub->method('startEmailVerification')
            ->willThrowException(new VerificationMailNotSentException());

        $sut = $this->getSut(settings: $settingsStub, verificationService: $verificationServiceStub);

        $this->assertTrue($sut->initializeEmailVerification($this->createStub(User::class)));
    }

    #[Test]
    public function rememberPendingUser(): void
    {
        $pendingUserSessionSpy = $this->createMock(PendingUserSessionInterface::class);
        $pendingUserSessionSpy->expects($this->once())->method('setPendingUserId')->with($userId = uniqid());

        $sut = $this->getSut(pendingUserSession: $pendingUserSessionSpy);

        $sut->rememberPendingUser($userId);
    }

    #[Test]
    public function stopUnverifiedLogin(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByUserId' => $this->createStub(PendingVerificationDtoInterface::class),
        ]);
        $pendingUserSessionSpy = $this->createMock(PendingUserSessionInterface::class);
        $pendingUserSessionSpy->expects($this->once())->method('setPendingUserId')->with($userId = uniqid());
        $urlBuilderStub = $this->createConfiguredStub(VerificationUrlBuilderInterface::class, [
            'buildPendingPageUrl' => $url = uniqid(),
        ]);

        $sut = $this->getSut(
            settings: $settingsStub,
            pendingRepository: $pendingRepositoryStub,
            pendingUserSession: $pendingUserSessionSpy,
            urlBuilder: $urlBuilderStub,
        );

        try {
            $sut->stopUnverifiedLogin($userId);
            $this->fail('EmailNotVerifiedException expected.');
        } catch (EmailNotVerifiedException $exception) {
            $this->assertSame($userId, $exception->getUserId());
            $this->assertSame($url, $exception->getVerificationUrl());
        }
    }

    #[Test]
    public function stopUnverifiedLoginLetsAConfirmedUserLogIn(): void
    {
        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'isEmailVerificationEnabled' => true,
        ]);
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByUserId' => null,
        ]);
        $pendingUserSessionSpy = $this->createMock(PendingUserSessionInterface::class);
        $pendingUserSessionSpy->expects($this->never())->method('setPendingUserId');

        $sut = $this->getSut(
            settings: $settingsStub,
            pendingRepository: $pendingRepositoryStub,
            pendingUserSession: $pendingUserSessionSpy,
        );

        $sut->stopUnverifiedLogin(uniqid());
    }

    private function getSut(
        ?EmailVerificationSettingsInterface $settings = null,
        ?PendingVerificationRepositoryInterface $pendingRepository = null,
        ?EmailVerificationServiceInterface $verificationService = null,
        ?PendingUserSessionInterface $pendingUserSession = null,
        ?VerificationUrlBuilderInterface $urlBuilder = null,
    ): UserEmailVerificationFacadeInterface {
        return new UserEmailVerificationFacade(
            settings: $settings ?? $this->createStub(EmailVerificationSettingsInterface::class),
            pendingRepository: $pendingRepository ?? $this->createStub(PendingVerificationRepositoryInterface::class),
            verificationService: $verificationService ?? $this->createStub(EmailVerificationServiceInterface::class),
            pendingUserSession: $pendingUserSession ?? $this->createStub(PendingUserSessionInterface::class),
            urlBuilder: $urlBuilder ?? $this->createStub(VerificationUrlBuilderInterface::class),
        );
    }
}
