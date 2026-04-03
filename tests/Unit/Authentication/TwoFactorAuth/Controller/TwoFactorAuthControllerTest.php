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
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
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

    public function testResendCodeSendsSuccessResponse(): void
    {
        $authServiceMock = $this->createMock(AuthorizeServiceInterface::class);
        $authServiceMock->expects($this->once())
            ->method('resend')
            ->willReturn(true);

        $jsonResponseMock = $this->createMock(JsonResponseInterface::class);
        $jsonResponseMock->expects($this->never())
            ->method('setStatusCode');
        $jsonResponseMock->expects($this->once())
            ->method('send')
            ->with(['success' => true]);

        $controller = $this->getSut(
            authService: $authServiceMock,
            jsonResponse: $jsonResponseMock,
        );

        $controller->resendCode();
    }

    public function testResendCodeSends429WhenCooldownActive(): void
    {
        $authServiceMock = $this->createMock(AuthorizeServiceInterface::class);
        $authServiceMock->expects($this->once())
            ->method('resend')
            ->willReturn(false);

        $jsonResponseMock = $this->createMock(JsonResponseInterface::class);
        $jsonResponseMock->expects($this->once())
            ->method('setStatusCode')
            ->with(429);
        $jsonResponseMock->expects($this->once())
            ->method('send')
            ->with(['success' => false]);

        $controller = $this->getSut(
            authService: $authServiceMock,
            jsonResponse: $jsonResponseMock,
        );

        $controller->resendCode();
    }

    private function getSut(
        TwoFAServiceInterface $twoFAService = null,
        TwoFAUserServiceInterface $twoFAUserService = null,
        AuthorizeServiceInterface $authService = null,
        AuthCodeRequestInterface $authCodeRequest = null,
        UtilsView $utilsView = null,
        JsonResponseInterface $jsonResponse = null,
    ): TwoFactorAuthController {
        return new TwoFactorAuthController(
            twoFAService: $twoFAService ?? $this->createStub(TwoFAServiceInterface::class),
            twoFAUserService: $twoFAUserService ?? $this->createStub(TwoFAUserServiceInterface::class),
            authService: $authService ?? $this->createStub(AuthorizeServiceInterface::class),
            authCodeRequest: $authCodeRequest ?? $this->createStub(AuthCodeRequestInterface::class),
            utilsView: $utilsView ?? $this->createStub(UtilsView::class),
            jsonResponse: $jsonResponse ?? $this->createStub(JsonResponseInterface::class),
        );
    }
}
