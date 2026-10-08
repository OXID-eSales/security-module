<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationUrlBuilder;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationUrlBuilderInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VerificationUrlBuilderTest extends TestCase
{
    #[Test]
    public function buildVerificationLink(): void
    {
        $configStub = $this->createConfiguredStub(Config::class, [
            'getShopSecureHomeUrl' => $shopUrl = uniqid() . '?',
        ]);

        $sut = $this->getSut(config: $configStub);

        $this->assertSame(
            $shopUrl . 'cl=oesm_email_verification&fnc=verifyEmail&token=' . ($token = uniqid()),
            $sut->buildVerificationLink($token)
        );
    }

    #[Test]
    public function buildPendingPageUrl(): void
    {
        $configStub = $this->createConfiguredStub(Config::class, [
            'getShopSecureHomeUrl' => $shopUrl = uniqid() . '?',
        ]);

        $sut = $this->getSut(config: $configStub);

        $this->assertSame($shopUrl . 'cl=oesm_email_verification&state=pending', $sut->buildPendingPageUrl());
    }

    #[Test]
    public function buildPendingPageUrlDecodesTheHtmlEncodedShopUrl(): void
    {
        $shopUrl = uniqid() . '?lang=' . rand() . '&';
        $configStub = $this->createConfiguredStub(Config::class, [
            'getShopSecureHomeUrl' => htmlspecialchars($shopUrl),
        ]);

        $sut = $this->getSut(config: $configStub);

        $this->assertSame($shopUrl . 'cl=oesm_email_verification&state=pending', $sut->buildPendingPageUrl());
    }

    private function getSut(?Config $config = null): VerificationUrlBuilderInterface
    {
        return new VerificationUrlBuilder(
            config: $config ?? $this->createStub(Config::class),
        );
    }
}
