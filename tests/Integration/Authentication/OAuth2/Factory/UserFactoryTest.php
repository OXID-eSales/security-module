<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\Factory\UserFactory;

class UserFactoryTest extends IntegrationTestCase
{
    public function testCreateArticle(): void
    {
        $articleFactory = new UserFactory();
        $article = $articleFactory->create();

        $this->assertInstanceOf(User::class, $article);
    }
}
