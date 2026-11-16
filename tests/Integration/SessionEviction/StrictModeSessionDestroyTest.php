<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class StrictModeSessionDestroyTest extends IntegrationTestCase
{
    use SessionEvictionTestTrait;

    private const MARKER = 'oesmSessionEvictionMarker';

    #[Test]
    public function strictModeDestroysASessionWithoutToken(): void
    {
        $this->startSession($this->createUser()->getId());
        $this->session()->set(self::MARKER, uniqid('marker'));
        $this->storeBasketWithArticle();

        $this->runWithSessionEviction(true, true, function (): void {
            $this->assertNull($this->nextRequestUserId());
        });

        $this->assertNull($this->session()->get('usr'));
        $this->assertNull($this->session()->get(self::MARKER));
        $this->assertNull($this->session()->get($this->basketName()));
    }

    #[Test]
    public function strictModeKeepsASessionWithValidToken(): void
    {
        $user = $this->createUser();
        $this->startSession($user->getId());
        $user->setSessionLoginToken();
        $this->session()->set(self::MARKER, $marker = uniqid('marker'));

        $this->runWithSessionEviction(true, true, function () use ($user): void {
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });

        $this->assertSame($marker, $this->session()->get(self::MARKER));
    }

    #[Test]
    public function nonStrictModeKeepsASessionWithoutToken(): void
    {
        $user = $this->createUser();
        $this->startSession($user->getId());
        $this->session()->set(self::MARKER, $marker = uniqid('marker'));

        $this->runWithSessionEviction(true, false, function () use ($user): void {
            $this->assertSame($user->getId(), $this->nextRequestUserId());
        });

        $this->assertSame($marker, $this->session()->get(self::MARKER));
    }

    private function storeBasketWithArticle(): void
    {
        $basket = oxNew(Basket::class);
        $basket->addToBasket($this->createArticle(), 1);
        $this->session()->set($this->basketName(), serialize($basket));
    }

    private function basketName(): string
    {
        return Registry::getConfig()->getShopId() . '_basket';
    }
}
