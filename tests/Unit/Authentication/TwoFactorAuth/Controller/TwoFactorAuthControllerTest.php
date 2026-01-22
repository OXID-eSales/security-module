<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Controller;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller\TwoFactorAuthController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthControllerTest extends TestCase
{
    public function testResendCodeCallsGenerate(): void
    {
        $authService = $this->createMock(AuthorizeServiceInterface::class);
        $authService->expects($this->once())
            ->method('generate');

        $controller = new TwoFactorAuthController(
            authService: $authService,
            userService: $this->createMock(UserServiceInterface::class),
            authCodeRequest: $this->createMock(AuthCodeRequestInterface::class),
        );

        $controller->resendCode();
    }
}
