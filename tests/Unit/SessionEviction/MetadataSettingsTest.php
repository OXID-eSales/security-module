<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction;

use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MetadataSettingsTest extends TestCase
{
    #[Test]
    #[DataProvider('sessionEvictionSettingsDataProvider')]
    public function sessionEvictionSettingIsRegisteredInMetadata(string $name): void
    {
        $setting = $this->findSetting($name);

        $this->assertNotNull($setting, "Setting '$name' is not registered in metadata.php");
        $this->assertSame('session_eviction', $setting['group']);
        $this->assertSame('bool', $setting['type']);
        $this->assertFalse($setting['value']);
    }

    public static function sessionEvictionSettingsDataProvider(): array
    {
        return [
            'enable setting' => [ModuleSettingsService::SESSION_EVICTION_ENABLE],
            'strict setting' => [ModuleSettingsService::SESSION_EVICTION_STRICT],
        ];
    }

    private function findSetting(string $name): ?array
    {
        foreach ($this->loadSettings() as $setting) {
            if (($setting['name'] ?? null) === $name) {
                return $setting;
            }
        }

        return null;
    }

    private function loadSettings(): array
    {
        $load = static function (): array {
            require __DIR__ . '/../../../metadata.php';
            /** @var array $aModule */
            return $aModule['settings'] ?? [];
        };

        return $load();
    }
}
