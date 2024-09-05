<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorTypeException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateExceptionInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordValidatorChain;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\PasswordValidatorInterface;
use PHPUnit\Framework\TestCase;

class PasswordValidatorChainTest extends TestCase
{
    public function testWrongValidatorType(): void
    {
        $this->expectException(InvalidValidatorTypeException::class);

        new PasswordValidatorChain([new \stdClass()]);
    }

    public function testValidationWithoutValidators(): void
    {
        $passwordValidatorChain = new PasswordValidatorChain([]);
        $passwordValidatorChain->validatePassword(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testValidationWithInactiveValidatorDoesNotTriggerValidation(): void
    {
        $validatorMock = $this->createMock(PasswordValidatorInterface::class);
        $validatorMock->method('isEnabled')->willReturn(false);
        $validatorMock->expects($this->never())->method('validate');

        $passwordValidatorChain = new PasswordValidatorChain([$validatorMock]);
        $passwordValidatorChain->validatePassword(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testValidatorWillThrowFirstFailingValidatorException(): void
    {
        $validator1Mock = $this->createMock(PasswordValidatorInterface::class);
        $validator1Mock->method('isEnabled')->willReturn(true);

        $validator2Mock = $this->createMock(PasswordValidatorInterface::class);
        $validator2Mock->method('isEnabled')->willReturn(true);

        $expectedException = new class extends \Exception implements PasswordValidateExceptionInterface {};
        $validator2Mock->method('validate')->willThrowException($expectedException);

        $validator3Mock = $this->createMock(PasswordValidatorInterface::class);
        $validator3Mock->method('isEnabled')->willReturn(true);
        $validator3Mock->method('validate')->willThrowException(new \Exception());

        $this->expectException(PasswordSpecialCharException::class);

        $passwordValidatorChain = new PasswordValidatorChain([
            $validator1Mock,
            $validator2Mock,
            $validator3Mock,
        ]);

        $this->expectExceptionObject($expectedException);
        $passwordValidatorChain->validatePassword(uniqid());
    }
}
