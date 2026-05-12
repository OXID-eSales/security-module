<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorTypeException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordCollectionException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordUpperCaseException;
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

    public function testValidatorWillThrowAllFailingValidatorExceptions(): void
    {
        $validator1Stub = $this->createStub(PasswordValidatorInterface::class);
        $validator1Stub->method('isEnabled')->willReturn(true);
        $validator1Stub->method('validate')
            ->willThrowException(new PasswordUpperCaseException());

        $validator2Stub = $this->createStub(PasswordValidatorInterface::class);
        $validator2Stub->method('isEnabled')->willReturn(true);
        $validator2Stub->method('validate')
            ->willThrowException(new PasswordMinimumLengthException(8));

        $passwordValidatorChain = new PasswordValidatorChain([
            $validator1Stub,
            $validator2Stub,
        ]);

        try {
            $passwordValidatorChain->validatePassword(uniqid());
        } catch (PasswordCollectionException $e) {
            $exceptions = $e->getValidationExceptions();

            $this->assertCount(2, $exceptions);
            $this->assertInstanceOf(PasswordUpperCaseException::class, $exceptions[0]);
            $this->assertInstanceOf(PasswordMinimumLengthException::class, $exceptions[1]);
        }
    }
}
