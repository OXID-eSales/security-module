<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\FormSecurity\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\FormSecurity\Service\SessionlessHiddenParamsBuilder;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class SessionlessHiddenParamsBuilderTest extends IntegrationTestCase
{
    #[Test]
    public function buildKeepsSessionIdInputButDropsStokenWhenSidIsNeeded(): void
    {
        // When the shop needs the sid in forms (e.g. cookieless sessions), the sid
        // hidden input must survive while the stoken is stripped.
        $sessionStub = $this->createStub(Session::class);
        $sessionStub->method('isSidNeeded')->willReturn(true);
        $sessionStub->method('getName')->willReturn('sid');
        $sessionStub->method('getId')->willReturn('abc123');
        Registry::set(Session::class, $sessionStub);

        $result = (new SessionlessHiddenParamsBuilder())->build('');

        $this->assertStringContainsString('name="sid" value="abc123"', $result);
        $this->assertStringNotContainsString('stoken', $result);
    }

    #[Test]
    public function buildOmitsSessionIdInputWhenSidIsNotNeeded(): void
    {
        $sessionStub = $this->createStub(Session::class);
        $sessionStub->method('isSidNeeded')->willReturn(false);
        $sessionStub->method('getName')->willReturn('sid');
        Registry::set(Session::class, $sessionStub);

        $result = (new SessionlessHiddenParamsBuilder())->build('');

        $this->assertStringNotContainsString('name="sid"', $result);
        $this->assertStringNotContainsString('stoken', $result);
    }
}
