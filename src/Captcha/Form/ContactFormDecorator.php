<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Form;

use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\Form;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;

class ContactFormDecorator
{
    public function __construct(
        private ContactFormBridgeInterface $contactFormBridge,
        private ContactFormCaptchaValidator $contactFormCaptchaValidator
    ) {
    }

    public function getContactForm(): FormInterface
    {
        /** @var Form $contactForm */
        $contactForm = $this->contactFormBridge->getContactForm();

        $contactForm->addValidator($this->contactFormCaptchaValidator);

        return $contactForm;
    }

    public function getContactFormMessage(): string
    {
        $contactForm = $this->contactFormBridge->getContactForm();

        return $this->contactFormBridge->getContactFormMessage($contactForm);
    }
}
