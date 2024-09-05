<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\InputValidator;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordDigitException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordLowerCaseException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordUpperCaseException;
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
        $this->assertSame(
            (new $expectedException())->getMessage(),
            $exception->getMessage()
        );
    }

    public static function dataProviderPasswordError(): iterable
    {
        yield ['short', PasswordMinimumLengthException::class];
        yield ['12345678', PasswordSpecialCharException::class];
        yield ['password!', PasswordDigitException::class];
        yield ['passw0rd!', PasswordUpperCaseException::class];
        yield ['PASSW0RD!', PasswordLowerCaseException::class];
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
