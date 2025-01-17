<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Shop;

use OxidEsales\Eshop\Application\Controller\NewsletterController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Shared\Core\InputValidator;
use PHPUnit\Framework\TestCase;

class NewsletterControllerTest extends TestCase
{
    public function testSendWithValidCaptchaCallsParentSend(): void
    {
        $inputValidator = $this->createMock(InputValidator::class);
        $inputValidator->expects($this->once())
            ->method('validateCaptchaField');

        $newsletterController = $this->getMockBuilder(NewsletterController::class)
            ->setMethods(['send']) // Mock parent::send()
            ->getMock();
        $newsletterController->expects($this->once())
            ->method('send');

        $newsletterController->send();
    }

    public function testSend_WithInvalidCaptcha_AddsErrorToDisplay(): void
    {
        $exception = new CaptchaValidateException('ERROR_INVALID_CAPTCHA');
        $inputValidator = $this->createMock(InputValidator::class);
        $inputValidator->expects($this->once())
            ->method('validateCaptchaField')
            ->willThrowException($exception);

        $utilsView = $this->createMock(UtilsView::class);
        $utilsView->expects($this->once())
            ->method('addErrorToDisplay')
            ->with('ERROR_INVALID_CAPTCHA');

        $newsletterController = $this->getMockBuilder(NewsletterController::class)
            ->setMethods(['send']) // Mock parent::send()
            ->getMock();
        $newsletterController->expects($this->never())
            ->method('send');

        $newsletterController->send();
    }
}
