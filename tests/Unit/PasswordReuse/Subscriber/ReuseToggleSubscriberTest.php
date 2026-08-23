<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordReuse\Subscriber;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Event\SettingChangedEvent;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Repository\PasswordHistoryRepositoryInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\PasswordReuse\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Subscriber\ReuseToggleSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ReuseToggleSubscriberTest extends TestCase
{
    private const OTHER_MODULE = 'some_other_module';
    private const OTHER_SETTING = 'oeSecuritySomethingElse';

    #[Test]
    public function subscribesToSettingChangedEvent(): void
    {
        $this->assertArrayHasKey(
            SettingChangedEvent::class,
            ReuseToggleSubscriber::getSubscribedEvents(),
        );
    }

    #[Test]
    public function disablingReusePreventionPurgesAllHistory(): void
    {
        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->once())->method('purgeAll');

        $sut = $this->getSut(
            settings: $this->settings(enabled: false),
            repository: $repositorySpy,
        );

        $sut->onSettingChanged($this->reuseEvent());
    }

    #[Test]
    public function enablingReusePreventionDoesNotPurge(): void
    {
        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->never())->method('purgeAll');

        $sut = $this->getSut(
            settings: $this->settings(enabled: true),
            repository: $repositorySpy,
        );

        $sut->onSettingChanged($this->reuseEvent());
    }

    #[Test]
    public function unrelatedSettingOfOurModuleIsIgnored(): void
    {
        $settingsSpy = $this->createMock(ModuleSettingsServiceInterface::class);
        $settingsSpy->expects($this->never())->method('isReusePreventionEnabled');
        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->never())->method('purgeAll');

        $sut = $this->getSut(settings: $settingsSpy, repository: $repositorySpy);

        $sut->onSettingChanged(
            new SettingChangedEvent(self::OTHER_SETTING, 1, Module::MODULE_ID),
        );
    }

    #[Test]
    public function reuseSettingOfAnotherModuleIsIgnored(): void
    {
        $settingsSpy = $this->createMock(ModuleSettingsServiceInterface::class);
        $settingsSpy->expects($this->never())->method('isReusePreventionEnabled');
        $repositorySpy = $this->createMock(PasswordHistoryRepositoryInterface::class);
        $repositorySpy->expects($this->never())->method('purgeAll');

        $sut = $this->getSut(settings: $settingsSpy, repository: $repositorySpy);

        $sut->onSettingChanged(
            new SettingChangedEvent(ModuleSettingsService::REUSE_PREVENTION_ENABLE, 1, self::OTHER_MODULE),
        );
    }

    #[Test]
    public function purgeFailureIsSwallowedAndLogged(): void
    {
        $repositoryStub = $this->createStub(PasswordHistoryRepositoryInterface::class);
        $repositoryStub->method('purgeAll')->willThrowException(new RuntimeException('table down'));
        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(
            settings: $this->settings(enabled: false),
            repository: $repositoryStub,
            logger: $loggerSpy,
        );

        $sut->onSettingChanged($this->reuseEvent());
    }

    private function reuseEvent(): SettingChangedEvent
    {
        return new SettingChangedEvent(ModuleSettingsService::REUSE_PREVENTION_ENABLE, 1, Module::MODULE_ID);
    }

    private function settings(bool $enabled): ModuleSettingsServiceInterface
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn($enabled);

        return $settingsStub;
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?PasswordHistoryRepositoryInterface $repository = null,
        ?LoggerInterface $logger = null,
    ): ReuseToggleSubscriber {
        return new ReuseToggleSubscriber(
            $settings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            $repository ?? $this->createStub(PasswordHistoryRepositoryInterface::class),
            $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
