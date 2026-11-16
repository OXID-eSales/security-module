<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Shared\Controller\ForgotPasswordController as ModuleForgotPasswordController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ForgotPasswordUpdatePasswordTokenTest extends IntegrationTestCase
{
    use SessionEvictionTestTrait;

    private const RESET_SUCCESS_ACTION = 'forgotpwd?success=1';

    #[Test]
    public function resetUserStaysLoggedInWithStrictMode(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession();
        $this->sendResetForm($user);

        $this->runWithSessionEviction(true, true, function () use ($user): void {
            $this->assertSame(self::RESET_SUCCESS_ACTION, $this->getSut()->updatePassword());
            $this->assertNotSame('', (string) $this->session()->get('login-token'));
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });
    }

    #[Test]
    public function resetReplacesTheTokenOfAnotherUserLoggedInInTheSameBrowser(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession(uniqid('otherUserId'), uniqid('loginToken'));
        $this->sendResetForm($user);

        $this->runWithSessionEviction(true, true, function () use ($user): void {
            $this->assertSame(self::RESET_SUCCESS_ACTION, $this->getSut()->updatePassword());
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });
    }

    #[Test]
    public function resetSetsNoTokenWhenTheFeatureIsDisabled(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession();
        $this->sendResetForm($user);

        $this->runWithSessionEviction(false, false, function (): void {
            $this->assertSame(self::RESET_SUCCESS_ACTION, $this->getSut()->updatePassword());
        });

        $this->assertNull($this->session()->get('login-token'));
    }

    #[Test]
    public function failedResetSetsNoToken(): void
    {
        $user = $this->createUserWithUpdateId();
        $this->startSession();
        $this->sendResetForm($user, 'Aa1!' . uniqid('password'));

        $this->runWithSessionEviction(true, true, function (): void {
            $this->assertNotSame(self::RESET_SUCCESS_ACTION, $this->getSut()->updatePassword());
        });

        $this->assertNull($this->session()->get('login-token'));
        $this->assertNull($this->session()->get('usr'));
    }

    private function sendResetForm(UserModel $user, ?string $confirmation = null): void
    {
        $password = 'Aa1!' . uniqid('password');

        $this->sendRequest([
            'uid' => $user->getUpdateId(),
            'password_new' => $password,
            'password_new_confirm' => $confirmation ?? $password,
        ]);
    }

    private function getSut(): ModuleForgotPasswordController
    {
        return oxNew(ModuleForgotPasswordController::class);
    }
}
