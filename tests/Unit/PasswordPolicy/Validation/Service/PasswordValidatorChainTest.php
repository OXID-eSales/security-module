<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorType;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordValidatorChain;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\PasswordValidatorInterface;
use PHPUnit\Framework\TestCase;

class PasswordValidatorChainTest extends TestCase
{
    public function testWrongValidatorType(): void
    {
        $this->expectException(InvalidValidatorType::class);

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
        $validator->method('validate')->willThrowException(new PasswordValidate());

        $this->expectException(PasswordValidate::class);

        $passwordValidatorChain = new PasswordValidatorChain([$validator]);
        $passwordValidatorChain->validatePassword(uniqid());
    }
}
