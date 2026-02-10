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
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\JsonResponseInterface;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthControllerTest extends TestCase
{
    public function testHandleOTPValidatesCodeAndFinalizesLogin(): void
    {
        $code = '123456';

        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn($code);

        $authServiceMock = $this->createMock(AuthorizeServiceInterface::class);
        $authServiceMock->expects($this->once())
            ->method('validate')
            ->with($code);

        $userServiceMock = $this->createMock(UserServiceInterface::class);
        $userServiceMock->expects($this->once())
            ->method('finalizeLogin');

        $controller = $this->getSut(
            authService: $authServiceMock,
            userService: $userServiceMock,
            authCodeRequest: $authCodeRequestStub,
        );

        $result = $controller->handleOTP();

        $this->assertNull($result);
    }

    public function testHandleOTPDoesNotFinalizeLoginOnValidationFailure(): void
    {
        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn('invalid');

        $authServiceStub = $this->createStub(AuthorizeServiceInterface::class);
        $authServiceStub->method('validate')
            ->willThrowException(new InvalidCodeException());

        $userServiceMock = $this->createMock(UserServiceInterface::class);
        $userServiceMock->expects($this->never())
            ->method('finalizeLogin');

        $controller = $this->getSut(
            authService: $authServiceStub,
            userService: $userServiceMock,
            authCodeRequest: $authCodeRequestStub,
        );

        $result = $controller->handleOTP();

        $this->assertNull($result);
    }

    public function testHandleOTPDisplaysErrorOnValidationFailure(): void
    {
        $exception = new InvalidCodeException();

        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn('invalid');

        $authServiceStub = $this->createStub(AuthorizeServiceInterface::class);
        $authServiceStub->method('validate')
            ->willThrowException($exception);

        $utilsViewMock = $this->createMock(UtilsView::class);
        $utilsViewMock->expects($this->once())
            ->method('addErrorToDisplay')
            ->with($exception);

        $controller = $this->getSut(
            authService: $authServiceStub,
            authCodeRequest: $authCodeRequestStub,
            utilsView: $utilsViewMock,
        );

        $controller->handleOTP();
    }

    public function testHandleOTPPropagatesNonOTPExceptions(): void
    {
        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn('123456');

        $authServiceStub = $this->createStub(AuthorizeServiceInterface::class);
        $authServiceStub->method('validate')
            ->willThrowException(new \RuntimeException('Unexpected error'));

        $controller = $this->getSut(
            authService: $authServiceStub,
            authCodeRequest: $authCodeRequestStub,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected error');

        $controller->handleOTP();
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
        AuthorizeServiceInterface $authService = null,
        UserServiceInterface $userService = null,
        AuthCodeRequestInterface $authCodeRequest = null,
        UtilsView $utilsView = null,
        JsonResponseInterface $jsonResponse = null,
    ): TwoFactorAuthController {
        return new TwoFactorAuthController(
            authService: $authService ?? $this->createStub(AuthorizeServiceInterface::class),
            userService: $userService ?? $this->createStub(UserServiceInterface::class),
            authCodeRequest: $authCodeRequest ?? $this->createStub(AuthCodeRequestInterface::class),
            utilsView: $utilsView ?? $this->createStub(UtilsView::class),
            jsonResponse: $jsonResponse ?? $this->createStub(JsonResponseInterface::class),
        );
    }
}
