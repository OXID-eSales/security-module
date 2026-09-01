<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordReuse\Subscriber;

use DateTimeImmutable;
use DateTimeInterface;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelUpdateEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\BeforeModelUpdateEvent;
use OxidEsales\SecurityModule\PasswordReuse\DTO\AccountDataInterface;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Repository\StoredPasswordReaderInterface;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Time\ChangeClockInterface;
use OxidEsales\SecurityModule\PasswordReuse\Notifier\Email\PasswordChangeEmailNotifierInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\AccountTypeResolverInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\ConfirmedChangeRegistryInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordHistoryServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Subscriber\PasswordChangeSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PasswordChangeSubscriberTest extends TestCase
{
    private const USER_ID = 'user-42';
    private const OLD_HASH = 'old-hash';
    private const NEW_HASH = 'new-hash';

    #[Test]
    public function afterUpdateWithoutPriorArmingDoesNothing(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $sut = $this->getSut(history: $historySpy, notifier: $notifierSpy);
        $sut->handleAfterUpdate($this->afterEvent(self::NEW_HASH));
    }

    #[Test]
    public function unchangedPasswordValueDoesNothing(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::OLD_HASH);
    }

    #[Test]
    public function emptyOldHashIsInitialEstablishmentAndDoesNothing(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(''),
            history: $historySpy,
            notifier: $notifierSpy,
        );

        $this->fire($sut, oldStored: '', persisted: self::NEW_HASH);
    }

    #[Test]
    public function genuineChangeWithReuseOnRecordsSupersededHash(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->once())
            ->method('record')
            ->with(
                self::USER_ID,
                self::OLD_HASH,
                'malladmin',
                $this->isInstanceOf(DateTimeInterface::class),
            );

        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: false),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
            resolver: $this->resolver('malladmin'),
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function genuineChangeWithNotifyOnNotifies(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');

        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->once())
            ->method('notify')
            ->with(self::USER_ID, $this->isInstanceOf(DateTimeInterface::class));

        $sut = $this->getSut(
            settings: $this->settings(reuse: false, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function recordAndNotifyAreIndependentlyGated(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->once())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->once())->method('notify');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function legacyRehashOldHashIsSuppressedWhenNotConfirmed(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $registryMock = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registryMock->method('isConfirmed')->willReturn(false);
        $registryMock->expects($this->once())->method('clear')->with(self::USER_ID);

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
            registry: $registryMock,
            bridge: $this->bridge(needsRehash: true),
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function legacyRehashOldHashProceedsWhenConfirmedThenClears(): void
    {
        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->once())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->once())->method('notify');

        $registryMock = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registryMock->method('isConfirmed')->with(self::USER_ID)->willReturn(true);
        $registryMock->expects($this->once())->method('clear')->with(self::USER_ID);

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historySpy,
            notifier: $notifierSpy,
            registry: $registryMock,
            bridge: $this->bridge(needsRehash: true),
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function recordFailureIsLoggedAndSwallowed(): void
    {
        $historyStub = $this->createStub(PasswordHistoryServiceInterface::class);
        $historyStub->method('record')->willThrowException(new RuntimeException('db down'));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            reader: $this->reader(self::OLD_HASH),
            history: $historyStub,
            logger: $loggerSpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function notifyFailureIsLoggedAndSwallowed(): void
    {
        $notifierStub = $this->createStub(PasswordChangeEmailNotifierInterface::class);
        $notifierStub->method('notify')->willThrowException(new RuntimeException('mail down'));

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(
            settings: $this->settings(reuse: false, notify: true),
            reader: $this->reader(self::OLD_HASH),
            notifier: $notifierStub,
            logger: $loggerSpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function beforeUpdateDoesNotArmWhenBothTogglesOff(): void
    {
        $readerSpy = $this->createMock(StoredPasswordReaderInterface::class);
        $readerSpy->expects($this->never())->method('getStoredPasswordHash');

        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');

        $sut = $this->getSut(
            settings: $this->settings(reuse: false, notify: false),
            reader: $readerSpy,
            history: $historySpy,
        );

        $this->fire($sut, oldStored: self::OLD_HASH, persisted: self::NEW_HASH);
    }

    #[Test]
    public function beforeUpdateSwallowsThrowingToggleReadAndDoesNotArm(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willThrowException(new RuntimeException('settings down'));

        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(
            settings: $settingsStub,
            history: $historySpy,
            notifier: $notifierSpy,
            logger: $loggerSpy,
        );

        $sut->handleBeforeUpdate($this->beforeEvent());
        $sut->handleAfterUpdate($this->afterEvent(self::NEW_HASH));
    }

    #[Test]
    public function beforeUpdateSwallowsThrowingStoredHashReadAndDoesNotArm(): void
    {
        $readerStub = $this->createStub(StoredPasswordReaderInterface::class);
        $readerStub->method('getStoredPasswordHash')->willThrowException(new RuntimeException('reader down'));

        $historySpy = $this->createMock(PasswordHistoryServiceInterface::class);
        $historySpy->expects($this->never())->method('record');
        $notifierSpy = $this->createMock(PasswordChangeEmailNotifierInterface::class);
        $notifierSpy->expects($this->never())->method('notify');

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(
            settings: $this->settings(reuse: true, notify: true),
            history: $historySpy,
            notifier: $notifierSpy,
            reader: $readerStub,
            logger: $loggerSpy,
        );

        $sut->handleBeforeUpdate($this->beforeEvent());
        $sut->handleAfterUpdate($this->afterEvent(self::NEW_HASH));
    }

    private function fire(PasswordChangeSubscriber $sut, string $oldStored, string $persisted): void
    {
        $sut->handleBeforeUpdate($this->beforeEvent());
        $sut->handleAfterUpdate($this->afterEvent($persisted));
    }

    private function beforeEvent(): BeforeModelUpdateEvent
    {
        return new BeforeModelUpdateEvent($this->userModel(self::NEW_HASH));
    }

    private function afterEvent(string $persistedHash): AfterModelUpdateEvent
    {
        return new AfterModelUpdateEvent($this->userModel($persistedHash));
    }

    private function userModel(string $passwordHash): User&Stub
    {
        $userStub = $this->createStub(User::class);
        $userStub->method('getId')->willReturn(self::USER_ID);
        $userStub->method('getFieldData')->willReturn($passwordHash);

        return $userStub;
    }

    private function settings(bool $reuse, bool $notify): ModuleSettingsServiceInterface
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn($reuse);
        $settingsStub->method('isChangeNotificationEnabled')->willReturn($notify);

        return $settingsStub;
    }

    private function reader(?string $hash): StoredPasswordReaderInterface
    {
        $readerStub = $this->createStub(StoredPasswordReaderInterface::class);
        $readerStub->method('getStoredPasswordHash')->willReturn($hash);

        return $readerStub;
    }

    private function resolver(string $rights): AccountTypeResolverInterface
    {
        $accountStub = $this->createStub(AccountDataInterface::class);
        $accountStub->method('getRights')->willReturn($rights);

        $resolverStub = $this->createStub(AccountTypeResolverInterface::class);
        $resolverStub->method('resolveAccount')->willReturn($accountStub);

        return $resolverStub;
    }

    private function bridge(bool $needsRehash): PasswordServiceBridgeInterface
    {
        $bridgeStub = $this->createStub(PasswordServiceBridgeInterface::class);
        $bridgeStub->method('passwordNeedsRehash')->willReturn($needsRehash);

        return $bridgeStub;
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?ConfirmedChangeRegistryInterface $registry = null,
        ?PasswordHistoryServiceInterface $history = null,
        ?PasswordChangeEmailNotifierInterface $notifier = null,
        ?AccountTypeResolverInterface $resolver = null,
        ?PasswordServiceBridgeInterface $bridge = null,
        ?StoredPasswordReaderInterface $reader = null,
        ?LoggerInterface $logger = null,
    ): PasswordChangeSubscriber {
        return new PasswordChangeSubscriber(
            $settings ?? $this->settings(reuse: true, notify: true),
            $registry ?? $this->createStub(ConfirmedChangeRegistryInterface::class),
            $history ?? $this->createStub(PasswordHistoryServiceInterface::class),
            $notifier ?? $this->createStub(PasswordChangeEmailNotifierInterface::class),
            $resolver ?? $this->resolver('user'),
            $bridge ?? $this->bridge(needsRehash: false),
            $reader ?? $this->reader(self::OLD_HASH),
            $this->clock(),
            $logger ?? $this->createStub(LoggerInterface::class),
        );
    }

    private function clock(): ChangeClockInterface
    {
        $clockStub = $this->createStub(ChangeClockInterface::class);
        $clockStub->method('now')->willReturn(new DateTimeImmutable('2026-08-21 10:00:00'));

        return $clockStub;
    }
}
