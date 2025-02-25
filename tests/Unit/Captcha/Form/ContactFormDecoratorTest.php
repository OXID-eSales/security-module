<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Form;

use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\Form;
use OxidEsales\SecurityModule\Captcha\Form\ContactFormCaptchaValidator;
use OxidEsales\SecurityModule\Captcha\Form\ContactFormDecorator;
use PHPUnit\Framework\TestCase;

class ContactFormDecoratorTest extends TestCase
{
    public function testGetContactForm(): void
    {
        $formCaptchaValidator = $this->createMock(ContactFormCaptchaValidator::class);
        $form = $this->createMock(Form::class);
        $form
            ->expects($this->once())
            ->method('addValidator')
            ->with($formCaptchaValidator);

        $contactFormBridge = $this->createMock(ContactFormBridgeInterface::class);
        $contactFormBridge
            ->expects($this->once())
            ->method('getContactForm')
            ->willReturn($form);

        $decorator = new ContactFormDecorator(
            $contactFormBridge,
            $formCaptchaValidator
        );

        $result = $decorator->getContactForm();
        $this->assertSame($form, $result);
    }

    public function testGetContactFormMessage(): void
    {
        $expectedMessage = 'Test message';

        $formCaptchaValidator = $this->createMock(ContactFormCaptchaValidator::class);
        $form = $this->createMock(Form::class);

        $contactFormBridge = $this->createMock(ContactFormBridgeInterface::class);
        $contactFormBridge
            ->expects($this->once())
            ->method('getContactForm')
            ->willReturn($form);

        $contactFormBridge
            ->expects($this->once())
            ->method('getContactFormMessage')
            ->with($form)
            ->willReturn($expectedMessage);

        $decorator = new ContactFormDecorator(
            $contactFormBridge,
            $formCaptchaValidator
        );

        $result = $decorator->getContactFormMessage();
        $this->assertEquals($expectedMessage, $result);
    }
}
