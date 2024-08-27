<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\PasswordPolicy\Transput\Unit;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OxidEsales\SecurityModule\PasswordPolicy\Transput\Response
 */
class ResponseTest extends TestCase
{
    public function testRespondAsJson(): void
    {
        $exampleData = ['somekey' => 'someValue'];
        $jsonValue = json_encode($exampleData);

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())
            ->method('showMessageAndExit')
            ->with($jsonValue);

        $correctHeaderSet = false;
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$correctHeaderSet) {
            if (preg_match("@Content-Type:\s?application/json;\s?charset=UTF-8@i", $value)) {
                $correctHeaderSet = true;
            }
        });

        $sut = new Response($utilsMock);
        $sut->responseAsJson($exampleData);

        $this->assertTrue($correctHeaderSet);
    }
}
