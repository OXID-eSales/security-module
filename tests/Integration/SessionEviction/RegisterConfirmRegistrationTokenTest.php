<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\SecurityModule\Shared\Controller\RegisterController as ModuleRegisterController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class RegisterConfirmRegistrationTokenTest extends IntegrationTestCase
{
    use SessionEvictionTestTrait;

    private const CONFIRM_SUCCESS_ACTION = 'register?confirmstate=1';

    #[Test]
    public function confirmedUserStaysLoggedInWithStrictMode(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession();
        $this->sendRequest(['uid' => $user->getUpdateId()]);

        $this->runWithSessionEviction(true, true, function () use ($user): void {
            $this->assertSame(self::CONFIRM_SUCCESS_ACTION, $this->getSut()->confirmRegistration());
            $this->assertNotSame('', (string) $this->session()->get('login-token'));
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });
    }

    #[Test]
    public function confirmationReplacesTheTokenOfAnotherUserLoggedInInTheSameBrowser(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession(uniqid('otherUserId'), uniqid('loginToken'));
        $this->sendRequest(['uid' => $user->getUpdateId()]);

        $this->runWithSessionEviction(true, true, function () use ($user): void {
            $this->assertSame(self::CONFIRM_SUCCESS_ACTION, $this->getSut()->confirmRegistration());
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });
    }

    #[Test]
    public function confirmationSetsNoTokenWhenTheFeatureIsDisabled(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession();
        $this->sendRequest(['uid' => $user->getUpdateId()]);

        $this->runWithSessionEviction(false, false, function (): void {
            $this->assertSame(self::CONFIRM_SUCCESS_ACTION, $this->getSut()->confirmRegistration());
        });

        $this->assertNull($this->session()->get('login-token'));
    }

    #[Test]
    public function failedConfirmationSetsNoToken(): void
    {
        $this->startSession();
        $this->sendRequest(['uid' => uniqid('updateId')]);

        $this->runWithSessionEviction(true, true, function (): void {
            $this->assertNotSame(self::CONFIRM_SUCCESS_ACTION, $this->getSut()->confirmRegistration());
        });

        $this->assertNull($this->session()->get('login-token'));
        $this->assertNull($this->session()->get('usr'));
    }

    private function getSut(): ModuleRegisterController
    {
        return oxNew(ModuleRegisterController::class);
    }
}
