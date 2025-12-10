<?php

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\OTPRequestInterface;

class TwoFactorAuthController extends FrontendController
{
    protected $_sThisTemplate = '@oe_security_module/templates/two_factor_auth';

    public function handleOTP(): void
    {
        $OTPRequest = $this->getService(OTPRequestInterface::class);

        //todo: catch only OTP exception that will be shown to user, maybe some abstract OTP exception?
        try {
            $authorizeService = $this->getService(AuthorizeServiceInterface::class);
            $authorizeService->validate(
                $OTPRequest->getOTPCode()
            );
        } catch (\Exception $e) {
            //todo: display error message to user
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
