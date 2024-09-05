<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysisService;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrengthService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PasswordStrengthServiceTest extends TestCase
{
    #[DataProvider('dataProviderPasswordStrength')]
    public function testPasswordStrengthEstimation(string $password, int $expectedStrength): void
    {
        $characterAnalysis = new CharacterAnalysisService();
        $passwordStrengthService = new PasswordStrengthService($characterAnalysis);
        $passwordStrength = $passwordStrengthService->estimateStrength($password);

        $this->assertSame($expectedStrength, $passwordStrength);
    }

    public static function dataProviderPasswordStrength(): iterable
    {
        yield ['123456', PasswordStrengthService::STRENGTH_VERY_WEAK];
        yield ['weak-password', PasswordStrengthService::STRENGTH_WEAK];
        yield ['medium-passw0rd', PasswordStrengthService::STRENGTH_MEDIUM];
        yield ['g00d-enough-passw0rd', PasswordStrengthService::STRENGTH_STRONG];
        yield ['very-str00ng-Pa55word!', PasswordStrengthService::STRENGTH_VERY_STRONG];
        yield ['very-str00ng-Pa55word¿' . chr(127), PasswordStrengthService::STRENGTH_VERY_STRONG];
    }
}
