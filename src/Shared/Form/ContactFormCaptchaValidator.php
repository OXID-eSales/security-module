<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Contact\Form;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormValidatorInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;

class ContactFormCaptchaValidator implements FormValidatorInterface
{
    /**
     * @var array
     */
    private $errors;

    public function __construct(private CaptchaServiceInterface $captchaService)
    {
    }

    /**
     * @param FormInterface $form
     * @return bool
     */
    public function isValid(FormInterface $form)
    {
        $captcha = Registry::getRequest()->getRequestParameter('captcha');

        try {
            $this->captchaService->validate($captcha);
        } catch (StandardException $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
