<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordReuse\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Repository\PasswordHistoryRepositoryInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordHistoryService;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordHistoryServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PasswordHistoryServiceTest extends TestCase
{
    #[Test]
    public function recordAppendsSupersededHashThenPrunesToResolvedSizeMinusOne(): void
    {
        $userId = uniqid('user_', true);
        $hash = uniqid('hash_', true);
        $rights = 'malladmin';
        $sizeN = mt_rand(3, 24);
        $supersededAt = new DateTimeImmutable();

        $settingsMock = $this->createMock(ModuleSettingsServiceInterface::class);
        $settingsMock->method('isReusePreventionEnabled')->willReturn(true);
        $settingsMock->expects($this->once())
            ->method('resolveCollectionSizeForRights')
            ->with($rights)
            ->willReturn($sizeN);

        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->once())
            ->method('append')
            ->with($userId, $hash, $supersededAt);
        $repositorySpy->expects($this->once())
            ->method('deleteSurplusBeyond')
            ->with($userId, $sizeN - 1);

        $sut = $this->getSut(settings: $settingsMock, repository: $repositorySpy);

        $sut->record($userId, $hash, $rights, $supersededAt);
    }

    #[Test]
    public function recordPrunesToZeroWhenResolvedSizeIsOne(): void
    {
        $userId = uniqid('user_', true);

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn(true);
        $settingsStub->method('resolveCollectionSizeForRights')->willReturn(1);

        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->once())->method('append');
        $repositorySpy->expects($this->once())
            ->method('deleteSurplusBeyond')
            ->with($userId, 0);

        $sut = $this->getSut(settings: $settingsStub, repository: $repositorySpy);

        $sut->record($userId, uniqid('hash_', true), 'user', new DateTimeImmutable());
    }

    #[Test]
    public function recordIsANoOpWhenReusePreventionIsDisabled(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn(false);

        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->never())->method('append');
        $repositorySpy->expects($this->never())->method('deleteSurplusBeyond');

        $sut = $this->getSut(settings: $settingsStub, repository: $repositorySpy);

        $sut->record(uniqid('user_', true), uniqid('hash_', true), 'user', new DateTimeImmutable());
    }

    #[Test]
    public function recordLogsAndSwallowsRepositoryFailure(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn(true);
        $settingsStub->method('resolveCollectionSizeForRights')->willReturn(mt_rand(3, 24));

        $repositoryMock = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositoryMock->method('append')->willThrowException(new RuntimeException('db down'));
        $repositoryMock->expects($this->never())->method('deleteSurplusBeyond');

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(settings: $settingsStub, repository: $repositoryMock, logger: $loggerSpy);

        $sut->record(uniqid('user_', true), uniqid('hash_', true), 'user', new DateTimeImmutable());
    }

    #[Test]
    public function purgeForUserDelegatesToRepository(): void
    {
        $userId = uniqid('user_', true);

        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->once())
            ->method('purgeForUser')
            ->with($userId);

        $sut = $this->getSut(repository: $repositorySpy);

        $sut->purgeForUser($userId);
    }

    #[Test]
    public function implementsInterface(): void
    {
        $this->assertInstanceOf(PasswordHistoryServiceInterface::class, $this->getSut());
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?PasswordHistoryRepositoryInterface $repository = null,
        ?LoggerInterface $logger = null,
    ): PasswordHistoryService {
        if ($settings === null) {
            $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
            $settingsStub->method('isReusePreventionEnabled')->willReturn(true);
            $settingsStub->method('resolveCollectionSizeForRights')->willReturn(mt_rand(3, 24));
            $settings = $settingsStub;
        }

        return new PasswordHistoryService(
            $settings,
            $repository ?? $this->createStub(PasswordHistoryRepositoryInterface::class),
            $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
