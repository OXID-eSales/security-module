<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\JsonResponse;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testSendSetsCorrectHeaders(): void
    {
        $utilsMock = $this->createMock(Utils::class);

        $headers = [];
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$headers) {
            $headers[] = $value;
        });
        $utilsMock->method('showMessageAndExit');

        $sut = new JsonResponse($utilsMock);
        $sut->send(['key' => 'value']);

        $this->assertContains('HTTP/1.1 200', $headers);
        $this->assertContains('Content-Type: application/json', $headers);
    }

    public function testSendOutputsJsonEncodedData(): void
    {
        $data = ['status' => 'ok', 'code' => 123];

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('showMessageAndExit')
            ->with(json_encode($data));

        $sut = new JsonResponse($utilsSpy);
        $sut->send($data);
    }

    public function testSendWithCustomStatusCode(): void
    {
        $utilsMock = $this->createMock(Utils::class);

        $headers = [];
        $utilsMock->method('setHeader')->willReturnCallback(function ($value) use (&$headers) {
            $headers[] = $value;
        });
        $utilsMock->method('showMessageAndExit');

        $sut = new JsonResponse($utilsMock);
        $sut->send([], 429);

        $this->assertContains('HTTP/1.1 429', $headers);
    }
}
