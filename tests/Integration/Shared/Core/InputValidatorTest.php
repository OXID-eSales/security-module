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
use OxidEsales\Eshop\Core\Registry;
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
        yield [
            'short',
            sprintf(Registry::getLang()->translateString('ERROR_PASSWORD_MIN_LENGTH'), 8)
        ];

        yield [
            '12345678',
            Registry::getLang()->translateString('ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER')
        ];

        yield [
            'password!',
            Registry::getLang()->translateString('ERROR_PASSWORD_MISSING_DIGIT')
        ];

        yield [
            'passw0rd!',
            Registry::getLang()->translateString('ERROR_PASSWORD_MISSING_UPPER_CASE')
        ];

        yield [
            'PASSW0RD!',
            Registry::getLang()->translateString('ERROR_PASSWORD_MISSING_LOWER_CASE')
        ];
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
        $this->assertSame(
            Registry::getLang()->translateString('ERROR_MESSAGE_PASSWORD_DO_NOT_MATCH'),
            $exception->getMessage()
        );
    }
}
