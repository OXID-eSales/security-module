<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\EmailVerification\Controller;

use DateTimeImmutable;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\SecurityModule\EmailVerification\Controller\EmailVerificationController;
use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationPageServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use OxidEsales\SecurityModule\EmailVerification\Transput\VerificationRequestInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class EmailVerificationControllerTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        unset($_GET['token'], $_GET['state']);
        $this->get(PendingUserSessionInterface::class)->clearPendingUserId();
        parent::tearDown();
    }

    #[Test]
    public function verifyEmail(): void
    {
        $userId = $this->createUser();
        $token = bin2hex(random_bytes(32));
        $this->get(PendingVerificationRepositoryInterface::class)->createPendingVerification(
            userId: $userId,
            token: $token,
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $_GET['token'] = $token;

        $sut = $this->getSut();
        $sut->verifyEmail();
        $sut->render();
        $page = $sut->getViewDataElement('verificationPage');

        $this->assertSame(VerificationPageState::VERIFIED, $page->getState());
        $this->assertFalse($page->canResend());
        $this->assertNull($this->get(PendingVerificationRepositoryInterface::class)->findByUserId($userId));
        $user = oxNew(UserModel::class);
        $user->load($userId);
        $this->assertNotEmpty($user->getFieldData('oeemailverified'));
    }

    #[Test]
    public function verifyEmailShowsExpiredForAnExpiredToken(): void
    {
        $userId = $this->createUser();
        $token = bin2hex(random_bytes(32));
        $this->get(PendingVerificationRepositoryInterface::class)->createPendingVerification(
            userId: $userId,
            token: $token,
            expiresAt: new DateTimeImmutable('-' . rand(1, 48) . ' hours'),
        );
        $_GET['token'] = $token;

        $sut = $this->getSut();
        $sut->verifyEmail();
        $sut->render();
        $page = $sut->getViewDataElement('verificationPage');

        $this->assertSame(VerificationPageState::EXPIRED, $page->getState());
        $this->assertTrue($page->canResend());
        $this->assertNotNull($this->get(PendingVerificationRepositoryInterface::class)->findByUserId($userId));
    }

    #[Test]
    public function render(): void
    {
        $this->get(PendingUserSessionInterface::class)->setPendingUserId(uniqid());
        $_GET['state'] = 'sent';

        $sut = $this->getSut();
        $sut->render();
        $page = $sut->getViewDataElement('verificationPage');

        $this->assertSame(VerificationPageState::SENT, $page->getState());
        $this->assertTrue($page->canResend());
        $this->assertSame(
            ContainerFactory::getInstance()->getContainer()->get(EmailVerificationSettingsInterface::class)
                ->getLinkLifetimeHours(),
            $sut->getViewDataElement('linkLifetimeHours')
        );
    }

    #[Test]
    public function renderShowsThePendingPageUnlessSentIsRequested(): void
    {
        $this->get(PendingUserSessionInterface::class)->setPendingUserId(uniqid());

        $sut = $this->getSut();
        $sut->render();
        $page = $sut->getViewDataElement('verificationPage');

        $this->assertSame(VerificationPageState::PENDING, $page->getState());
        $this->assertTrue($page->canResend());
    }

    #[Test]
    public function renderShowsInvalidWithoutAPendingUser(): void
    {
        $_GET['state'] = 'sent';

        $sut = $this->getSut();
        $sut->render();
        $page = $sut->getViewDataElement('verificationPage');

        $this->assertSame(VerificationPageState::INVALID, $page->getState());
        $this->assertFalse($page->canResend());
    }

    private function getSut(
        ?VerificationPageServiceInterface $pageService = null,
        ?EmailVerificationSettingsInterface $settings = null,
        ?VerificationRequestInterface $verificationRequest = null,
        ?Session $session = null,
    ): EmailVerificationController {
        return new EmailVerificationController(
            pageService: $pageService ?? $this->get(VerificationPageServiceInterface::class),
            settings: $settings ?? ContainerFactory::getInstance()->getContainer()
                ->get(EmailVerificationSettingsInterface::class),
            verificationRequest: $verificationRequest ?? $this->get(VerificationRequestInterface::class),
            session: $session ?? $this->createStub(Session::class),
        );
    }

    private function createUser(): string
    {
        $user = oxNew(UserModel::class);
        $user->setId(uniqid());
        $user->assign(['oxusername' => uniqid() . '@example.test']);
        $user->save();

        return $user->getId();
    }
}
