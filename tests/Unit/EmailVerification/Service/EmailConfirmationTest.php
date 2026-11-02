<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailConfirmation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailConfirmationTest extends TestCase
{
    #[Test]
    public function confirmEmail(): void
    {
        $userRepositorySpy = $this->createMock(UserVerificationRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())->method('markEmailVerified')->with($userId = uniqid());

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->expects($this->once())->method('deletePendingVerification')->with($userId);

        $sut = new EmailConfirmation(
            pendingRepository: $pendingRepositorySpy,
            userRepository: $userRepositorySpy,
        );

        $sut->confirmEmail($userId);
    }
}
