<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Infrastructure\Factory;

use League\OAuth2\Client\Provider\FacebookUser;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\OAuth2UserDTOFactory;
use PHPUnit\Framework\TestCase;

class OAuth2UserDTOFactoryTest extends TestCase
{
    public function testCreateFromFacebookUser(): void
    {
        $expectedFirstName = uniqid();
        $expectedLastName = uniqid();
        $expectedEmail = uniqid();

        $facebookUserStub = $this->createStub(FacebookUser::class);
        $facebookUserStub->method('getFirstName')->willReturn($expectedFirstName);
        $facebookUserStub->method('getLastName')->willReturn($expectedLastName);
        $facebookUserStub->method('getEmail')->willReturn($expectedEmail);

        $sut = new OAuth2UserDTOFactory();

        $result = $sut->createFromFacebookUser($facebookUserStub);

        $this->assertSame($expectedFirstName, $result->getFirstName());
        $this->assertSame($expectedLastName, $result->getLastName());
        $this->assertSame($expectedEmail, $result->getEmail());
    }
}
