<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Core;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Shared\Core\InputValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class InputValidatorTest extends IntegrationTestCase
{
    #[DataProvider('dataProviderPasswordError')]
    public function testInputValidationError($password, $expectedException): void
    {
        $userModelMock = $this->createMock(User::class);

        $validator = oxNew(InputValidator::class);
        $exception = $validator->checkPassword($userModelMock, $password, $password);

        $this->assertInstanceOf(InputException::class, $exception);
        $this->assertSame($expectedException, $exception->getMessage());
    }

    public static function dataProviderPasswordError(): iterable
    {
        yield ['short', 'Das Passwort muss mindestens 8 Zeichen lang sein.'];
        yield ['12345678', 'Das Passwort enthält keine Sonderzeichen.'];
        yield ['password!', 'Das Passwort enthält keine Ziffer.'];
        yield ['passw0rd!', 'Das Passwort enthält keine Großbuchstaben.'];
        yield ['PASSW0RD!', 'Das Passwort enthält keine Kleinbuchstaben.'];
    }

    public function testPasswordCheck(): void
    {
        $password = 'Str0ng-passw0rd!';

        $userModelMock = $this->createMock(User::class);

        $validator = oxNew(InputValidator::class);
        $result = $validator->checkPassword($userModelMock, $password, $password);

        $this->assertNull($result);
    }

    public function testShopPasswordCheck(): void
    {
        $password = 'Str0ng-passw0rd!';

        $userModelMock = $this->createMock(User::class);

        $validator = oxNew(InputValidator::class);
        $exception = $validator->checkPassword($userModelMock, $password, $password . rand());

        $this->assertInstanceOf(UserException::class, $exception);
        $this->assertSame('Fehler: Die Passwörter stimmen nicht überein.', $exception->getMessage());
    }
}
