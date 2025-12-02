<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Infrastructure;

use OxidEsales\SecurityModule\Captcha\Infrastructure\LanguageWrapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use OxidEsales\Eshop\Core\Language;

#[CoversClass(LanguageWrapper::class)]
class LanguageWrapperTest extends TestCase
{
    public function testGetCurrentLanguageAbbr(): void
    {
        $langAbbr = uniqid();
        $languageMock = $this->createConfiguredStub(Language::class, [
            'getLanguageAbbr' => $langAbbr,
        ]);

        $languageWrapper = new LanguageWrapper($languageMock);
        $actualLangAbbr = $languageWrapper->getCurrentLanguageAbbr();

        $this->assertSame($langAbbr, $actualLangAbbr);
    }
}
