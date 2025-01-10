<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\Captcha\Transput\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testRespondAsImage(): void
    {
        $exampleData = uniqid();

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with('data:image/jpeg;base64,' . $exampleData);

        $correctHeaderSet = false;
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$correctHeaderSet) {
            if (preg_match("@Content-Type:\s?text/html;\s?charset=UTF-8@i", $value)) {
                $correctHeaderSet = true;
            }
        });

        $sut = new Response($utilsMock);
        $sut->responseAsImage($exampleData);

        $this->assertTrue($correctHeaderSet);
    }
}
