<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    #[Test]
    public function sendSetsCorrectHeaders(): void
    {
        $utilsStub = $this->createStub(Utils::class);

        $headers = [];
        $utilsStub->method('setHeader')->willReturnCallback(function ($value) use (&$headers) {
            $headers[] = $value;
        });
        $utilsStub->method('showMessageAndExit');

        $this->getSut($utilsStub)->send(['key' => 'value']);

        $this->assertContains('HTTP/1.1 200', $headers);
        $this->assertContains('Content-Type: application/json', $headers);
    }

    #[Test]
    public function sendOutputsJsonEncodedData(): void
    {
        $data = ['status' => 'ok', 'code' => 123];

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('showMessageAndExit')
            ->with(json_encode($data));

        $this->getSut($utilsSpy)->send($data);
    }

    #[Test]
    public function sendWithCustomStatusCode(): void
    {
        $utilsStub = $this->createStub(Utils::class);

        $headers = [];
        $utilsStub->method('setHeader')->willReturnCallback(function ($value) use (&$headers) {
            $headers[] = $value;
        });
        $utilsStub->method('showMessageAndExit');

        $this->getSut($utilsStub)->send([], 429);

        $this->assertContains('HTTP/1.1 429', $headers);
    }

    private function getSut(Utils $utils): JsonResponse
    {
        return new JsonResponse($utils);
    }
}
