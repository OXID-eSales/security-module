<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller\TwoFactorAuthController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\JsonResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthControllerTest extends TestCase
{
    #[Test]
    public function handleOTPVerifiesCodeAndLoginsUser(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $twoFAUserServiceSpy = $this->createMock(TwoFAUserServiceInterface::class);
        $twoFAUserServiceSpy->method('getPendingUserId')->willReturn($userId);
        $twoFAUserServiceSpy->expects($this->once())
            ->method('loginUser')
            ->with($userId);

        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn($code);

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())
            ->method('verify')
            ->with($userId, $code);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            twoFAUserService: $twoFAUserServiceSpy,
            authCodeRequest: $authCodeRequestStub,
        );

        $sut->handleOTP();
    }

    #[Test]
    public function handleOTPDisplaysErrorOnInvalidCode(): void
    {
        $exception = new InvalidCodeException();

        $twoFAServiceStub = $this->createStub(TwoFAServiceInterface::class);
        $twoFAServiceStub->method('verify')->willThrowException($exception);

        $utilsViewSpy = $this->createMock(UtilsView::class);
        $utilsViewSpy->expects($this->once())
            ->method('addErrorToDisplay')
            ->with($exception);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceStub,
            utilsView: $utilsViewSpy,
        );

        $sut->handleOTP();
    }

    #[Test]
    public function resendCodeSendsSuccessResponse(): void
    {
        $twoFAUserServiceStub = $this->createStub(TwoFAUserServiceInterface::class);
        $twoFAUserServiceStub->method('getPendingUserId')
            ->willReturn($userId = uniqid());

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())
            ->method('resend')
            ->with($userId);

        $jsonResponseSpy = $this->createMock(JsonResponseInterface::class);
        $jsonResponseSpy->expects($this->once())
            ->method('send')
            ->with(['success' => true], 200);

        $this->getSut(
            twoFAService: $twoFAServiceSpy,
            twoFAUserService: $twoFAUserServiceStub,
            jsonResponse: $jsonResponseSpy,
        )->resendCode();
    }

    #[Test]
    public function resendCodeSends429OnCooldown(): void
    {
        $twoFAUserServiceStub = $this->createStub(TwoFAUserServiceInterface::class);
        $twoFAUserServiceStub->method('getPendingUserId')
            ->willReturn($userId = uniqid());

        $twoFAServiceStub = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceStub->expects($this->once())
            ->method('resend')
            ->with($userId)
            ->willThrowException(new ResendCooldownException());

        $jsonResponseSpy = $this->createMock(JsonResponseInterface::class);
        $jsonResponseSpy->expects($this->once())
            ->method('send')
            ->with(['success' => false], 429);

        $this->getSut(
            twoFAService: $twoFAServiceStub,
            twoFAUserService: $twoFAUserServiceStub,
            jsonResponse: $jsonResponseSpy,
        )->resendCode();
    }

    private function getSut(
        TwoFAServiceInterface $twoFAService = null,
        TwoFAUserServiceInterface $twoFAUserService = null,
        AuthCodeRequestInterface $authCodeRequest = null,
        UtilsView $utilsView = null,
        JsonResponseInterface $jsonResponse = null,
    ): TwoFactorAuthController {
        return new TwoFactorAuthController(
            twoFAService: $twoFAService ?? $this->createStub(TwoFAServiceInterface::class),
            twoFAUserService: $twoFAUserService ?? $this->createStub(TwoFAUserServiceInterface::class),
            authCodeRequest: $authCodeRequest ?? $this->createStub(AuthCodeRequestInterface::class),
            utilsView: $utilsView ?? $this->createStub(UtilsView::class),
            jsonResponse: $jsonResponse ?? $this->createStub(JsonResponseInterface::class),
        );
    }
}
