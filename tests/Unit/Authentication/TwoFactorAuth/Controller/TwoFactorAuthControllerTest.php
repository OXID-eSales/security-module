<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller\TwoFactorAuthController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
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

    public function testHandleOTPDisplaysTranslatedErrorOnValidationFailure(): void
    {
        $translatedMessage = 'The code is invalid';

        $authCodeRequestStub = $this->createStub(AuthCodeRequestInterface::class);
        $authCodeRequestStub->method('getCode')->willReturn('invalid');

        $authServiceStub = $this->createStub(AuthorizeServiceInterface::class);
        $authServiceStub->method('validate')
            ->willThrowException(new InvalidCodeException());

        $languageMock = $this->createMock(Language::class);
        $languageMock->expects($this->once())
            ->method('translateString')
            ->with('ERROR_INVALID_CODE')
            ->willReturn($translatedMessage);

        $utilsViewMock = $this->createMock(UtilsView::class);
        $utilsViewMock->expects($this->once())
            ->method('addErrorToDisplay')
            ->with($translatedMessage);

        $controller = $this->getSut(
            authService: $authServiceStub,
            authCodeRequest: $authCodeRequestStub,
            language: $languageMock,
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

    public function testResendCodeCallsGenerate(): void
    {
        $authServiceMock = $this->createMock(AuthorizeServiceInterface::class);
        $authServiceMock->expects($this->once())
            ->method('generate');

        $controller = $this->getSut(
            authService: $authServiceMock,
        );

        $controller->resendCode();
    }

    private function getSut(
        AuthorizeServiceInterface $authService = null,
        UserServiceInterface $userService = null,
        AuthCodeRequestInterface $authCodeRequest = null,
        Language $language = null,
        UtilsView $utilsView = null,
    ): TwoFactorAuthController {
        return new TwoFactorAuthController(
            authService: $authService ?? $this->createStub(AuthorizeServiceInterface::class),
            userService: $userService ?? $this->createStub(UserServiceInterface::class),
            authCodeRequest: $authCodeRequest ?? $this->createStub(AuthCodeRequestInterface::class),
            language: $language ?? $this->createStub(Language::class),
            utilsView: $utilsView ?? $this->createStub(UtilsView::class),
        );
    }
}
