<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Factory;

use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;
use OxidEsales\SecurityModule\Shared\Form\ContactFormCaptchaValidator;

class ContactFormDecorator
{
    public function __construct(
        private ContactFormBridgeInterface $contactFormBridge,
        private ContactFormCaptchaValidator $contactFormCaptchaValidator
    ) {
    }

    public function getContactForm()
    {
        $contactForm = $this->contactFormBridge->getContactForm();

        $contactForm->addValidator($this->contactFormCaptchaValidator);

        return $contactForm;
    }

    public function getContactFormMessage()
    {
        $contactForm = $this->contactFormBridge->getContactForm();

        return $this->contactFormBridge->getContactFormMessage($contactForm);
    }
}
