<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\SecurityModule\PasswordPolicy\Controller\PasswordAjaxController;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\ResponseInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrength;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrengthInterface;
use PHPUnit\Framework\TestCase;

class PasswordAjaxControllerTest extends TestCase
{
    public function testPasswordStrength(): void
    {
        $strength = PasswordStrength::STRENGTH_STRONG;

        $this->mockRequest();

        $sut = $this->getStub(
            $passwordStrengthStub = $this->createMock(PasswordStrengthInterface::class),
            $responseStub = $this->createMock(ResponseInterface::class),
        );

        $passwordStrengthStub
            ->method('estimateStrength')
            ->willReturn($strength);

        $responseStub->expects($this->once())
            ->method('responseAsJson')
            ->with([
                'strength' => $strength
            ]);

        $sut->passwordStrength();
    }

    private function getStub(
        PasswordStrengthInterface $passwordStrength = null,
        ResponseInterface $response = null,
    ) {
        $sut = $this->createPartialMock(PasswordAjaxController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [
                PasswordStrengthInterface::class,
                $passwordStrength ?? $this->createStub(PasswordStrengthInterface::class)
            ],
            [
                ResponseInterface::class,
                $response ?? $this->createStub(ResponseInterface::class)
            ],
        ]);

        return $sut;
    }

    protected function mockRequest(): void
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        $request
            ->method('getRequestParameter')
            ->willReturnMap([
                ['password', null, uniqid()]
            ]);

        Registry::set(Request::class, $request);
    }
}
