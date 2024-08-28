<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Controller;

use OxidEsales\SecurityModule\PasswordPolicy\Controller\PasswordAjaxController;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\RequestInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\ResponseInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrength;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrengthInterface;
use PHPUnit\Framework\TestCase;

class PasswordAjaxControllerTest extends TestCase
{
    public function testPasswordStrength(): void
    {
        $strength = PasswordStrength::STRENGTH_STRONG;

        $sut = $this->getStub(
            $this->createMock(RequestInterface::class),
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
        RequestInterface $request = null,
        PasswordStrengthInterface $passwordStrength = null,
        ResponseInterface $response = null,
    ) {
        $sut = $this->createPartialMock(PasswordAjaxController::class, ['getService']);
        $sut->method('getService')->willReturnMap([
            [
              RequestInterface::class,
                $request ?? $this->createMock(RequestInterface::class)
            ],
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
}
