<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Factory;

use OxidEsales\EshopCommunity\Internal\Framework\Form\FormFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormValidatorInterface;

class ContactFormDecorator implements FormFactoryInterface
{
    public function __construct(
        private FormFactoryInterface $contactFormFactory,
        private FormValidatorInterface $contactFormCaptchaValidator
    ) {
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        var_dump(46456);
        $form = $this->contactFormFactory->getForm();

        $form->addValidator($this->contactFormCaptchaValidator);

        return $form;
    }
}
