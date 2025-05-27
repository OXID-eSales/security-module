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
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FormConfigurationInterface;

class ContactFormDecorator
{
    public function __construct(
        private ContactFormBridgeInterface $contactFormBridge,
        private ContactFormCaptchaValidatorInterface $formCaptchaValidator
    ) {
    }

    public function getContactForm(): FormInterface
    {
        /** @var Form $contactForm */
        $contactForm = $this->contactFormBridge->getContactForm();

        $contactForm->addValidator($this->formCaptchaValidator);

        return $contactForm;
    }

    public function getContactFormMessage(FormInterface $form): string
    {
        return $this->contactFormBridge->getContactFormMessage($form);
    }

    public function getContactFormConfiguration(): FormConfigurationInterface
    {
        return $this->contactFormBridge->getContactFormConfiguration();
    }
}
