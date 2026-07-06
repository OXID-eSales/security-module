<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\FormSecurity\Service;

use OxidEsales\SecurityModule\FormSecurity\Service\SessionlessHiddenParamsBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class SessionlessHiddenParamsBuilderTest extends TestCase
{
    #[Test]
    public function buildCombinesFormLangAndAdditionalParameters(): void
    {
        $langHidden = '<input type="hidden" name="lang" value="0">';
        $additionalParams = '<input type="hidden" name="cnid" value="abc">';

        $sut = $this->getSut(formLang: $langHidden);

        $result = $sut->build($additionalParams);

        $this->assertStringContainsString($langHidden, $result);
        $this->assertStringContainsString($additionalParams, $result);
    }

    #[Test]
    public function buildReturnsAdditionalParametersOnlyWhenFormLangEmpty(): void
    {
        $additionalParams = '<input type="hidden" name="cnid" value="abc">';

        $sut = $this->getSut(formLang: '');

        $this->assertSame($additionalParams, $sut->build($additionalParams));
    }

    #[Test]
    public function buildReturnsEmptyStringWhenFormLangAndAdditionalParamsEmpty(): void
    {
        $sut = $this->getSut(formLang: '');

        $this->assertSame('', $sut->build(''));
    }

    #[Test]
    public function buildOmitsSidAndStokenWhenSidIsNotNeeded(): void
    {
        $langHidden = '<input type="hidden" name="lang" value="0">';
        $additionalParams = '<input type="hidden" name="cnid" value="abc">';

        $sut = $this->getSut(formLang: $langHidden, sidHiddenParameter: '');

        $result = $sut->build($additionalParams);

        $this->assertStringNotContainsString('stoken', $result);
        $this->assertStringNotContainsString('name="sid"', $result);
    }

    #[Test]
    public function buildIncludesSidButNeverStokenWhenSidIsNeeded(): void
    {
        $sidHidden = '<input type="hidden" name="sid" value="abc123" />';
        $langHidden = '<input type="hidden" name="lang" value="0">';
        $additionalParams = '<input type="hidden" name="cnid" value="abc">';

        $sut = $this->getSut(formLang: $langHidden, sidHiddenParameter: $sidHidden);

        $result = $sut->build($additionalParams);

        $this->assertStringContainsString($sidHidden, $result);
        $this->assertStringContainsString($langHidden, $result);
        $this->assertStringContainsString($additionalParams, $result);
        $this->assertStringNotContainsString('stoken', $result);
    }

    private function getSut(string $formLang, string $sidHiddenParameter = ''): SessionlessHiddenParamsBuilder
    {
        $sut = $this->getMockBuilder(SessionlessHiddenParamsBuilder::class)
            ->onlyMethods(['getFormLang', 'getSidHiddenParameter'])
            ->getMock();
        $sut->method('getFormLang')->willReturn($formLang);
        $sut->method('getSidHiddenParameter')->willReturn($sidHiddenParameter);

        return $sut;
    }
}
