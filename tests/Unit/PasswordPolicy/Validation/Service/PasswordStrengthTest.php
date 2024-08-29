<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PasswordStrengthTest extends TestCase
{
    #[DataProvider('dataProviderPasswordStrength')]
    public function testPasswordStrengthEstimation(string $password, int $expectedStrength): void
    {
        $characterAnalysis = new CharacterAnalysis();
        $passwordStrengthService = new PasswordStrength($characterAnalysis);
        $passwordStrength = $passwordStrengthService->estimateStrength($password);

        $this->assertSame($expectedStrength, $passwordStrength);
    }

    public static function dataProviderPasswordStrength(): iterable
    {
        yield ['123456', PasswordStrength::STRENGTH_VERY_WEAK];
        yield ['weak-password', PasswordStrength::STRENGTH_WEAK];
        yield ['medium-passw0rd', PasswordStrength::STRENGTH_MEDIUM];
        yield ['g00d-enough-passw0rd', PasswordStrength::STRENGTH_STRONG];
        yield ['very-str00ng-Pa55word!', PasswordStrength::STRENGTH_VERY_STRONG];
        yield ['very-str00ng-Pa55word¿' . chr(127), PasswordStrength::STRENGTH_VERY_STRONG];
    }
}
