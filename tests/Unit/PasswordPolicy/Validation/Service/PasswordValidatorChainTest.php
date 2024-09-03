<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorTypeException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
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

    public function testValidation(): void
    {
        $validator = $this->createStub(PasswordValidatorInterface::class);

        $passwordValidatorChain = new PasswordValidatorChain([$validator]);
        $passwordValidatorChain->validatePassword(uniqid());

        $this->addToAssertionCount(1);
    }

    public function testValidatorWillThrowException(): void
    {
        $validator = $this->createStub(PasswordValidatorInterface::class);
        $validator->method('isEnabled')->willReturn(true);
        $validator->method('validate')->willThrowException(new PasswordSpecialCharException());

        $this->expectException(PasswordSpecialCharException::class);

        $passwordValidatorChain = new PasswordValidatorChain([$validator]);
        $passwordValidatorChain->validatePassword(uniqid());
    }
}
