<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Controller;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\EmailVerification\Controller\EmailVerificationController;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationPageServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use OxidEsales\SecurityModule\EmailVerification\Transput\VerificationRequestInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    #[Test]
    public function verifyEmail(): void
    {
        $verificationRequestStub = $this->createConfiguredStub(VerificationRequestInterface::class, [
            'getToken' => $token = uniqid(),
        ]);
        $pageServiceSpy = $this->createMock(VerificationPageServiceInterface::class);
        $pageServiceSpy->expects($this->once())->method('verifyEmail')->with($token);

        $sut = $this->getSut(pageService: $pageServiceSpy, verificationRequest: $verificationRequestStub);

        $sut->verifyEmail();
    }

    #[Test]
    public function resendEmail(): void
    {
        $verificationRequestStub = $this->createConfiguredStub(VerificationRequestInterface::class, [
            'getToken' => $token = uniqid(),
        ]);
        $sessionStub = $this->createConfiguredStub(Session::class, ['checkSessionChallenge' => true]);
        $pageServiceSpy = $this->createMock(VerificationPageServiceInterface::class);
        $pageServiceSpy->expects($this->once())->method('resendEmail')->with($token);
        $pageServiceSpy->expects($this->never())->method('rejectResendEmail');

        $sut = $this->getSut(
            pageService: $pageServiceSpy,
            verificationRequest: $verificationRequestStub,
            session: $sessionStub,
        );

        $sut->resendEmail();
    }

    #[Test]
    public function resendEmailRejectsARequestWithoutAValidCsrfToken(): void
    {
        $verificationRequestStub = $this->createConfiguredStub(VerificationRequestInterface::class, [
            'getToken' => $token = uniqid(),
        ]);
        $sessionStub = $this->createConfiguredStub(Session::class, ['checkSessionChallenge' => false]);
        $pageServiceSpy = $this->createMock(VerificationPageServiceInterface::class);
        $pageServiceSpy->expects($this->never())->method('resendEmail');
        $pageServiceSpy->expects($this->once())->method('rejectResendEmail')->with($token);

        $sut = $this->getSut(
            pageService: $pageServiceSpy,
            verificationRequest: $verificationRequestStub,
            session: $sessionStub,
        );

        $sut->resendEmail();
    }

    private function getSut(
        ?VerificationPageServiceInterface $pageService = null,
        ?EmailVerificationSettingsInterface $settings = null,
        ?VerificationRequestInterface $verificationRequest = null,
        ?Session $session = null,
    ): EmailVerificationController {
        return new EmailVerificationController(
            pageService: $pageService ?? $this->createStub(VerificationPageServiceInterface::class),
            settings: $settings ?? $this->createStub(EmailVerificationSettingsInterface::class),
            verificationRequest: $verificationRequest ?? $this->createStub(VerificationRequestInterface::class),
            session: $session ?? $this->createStub(Session::class),
        );
    }
}
