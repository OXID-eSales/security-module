<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;

class TwoFactorAuthController extends FrontendController
{
    /**
     * Current view template
     *
     * @var string
     * @SuppressWarnings("PHPMD.CamelCasePropertyName")
     */
    protected $_sThisTemplate = '@oe_security_module/templates/two_factor_auth';

    public function handleOTP(): void
    {
        $OTPRequest = $this->getService(AuthCodeRequestInterface::class);

        //todo: catch only OTP exception that will be shown to user, maybe some abstract OTP exception?
        try {
            $authorizeService = $this->getService(AuthorizeServiceInterface::class);
            $authorizeService->validate(
                $OTPRequest->getCode()
            );

            //todo: redirect to originally requested page after successful OTP validation
            //todo: create correct session for logged in user (from service, handleLogin?)
        } catch (\Exception $e) {
            //todo: display translated error message to user
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        }
    }

    public function generate(): void
    {
        //todo: stop execution if not ajax
        //todo: prevent spam by rate limiting
        //todo: should return json response with success or error message
        $authorizeService = $this->getService(AuthorizeServiceInterface::class);
        $authorizeService->generate();
    }
}
