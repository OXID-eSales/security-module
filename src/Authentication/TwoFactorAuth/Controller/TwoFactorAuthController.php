<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
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

    public function __construct(
        private readonly AuthorizeServiceInterface $authService,
        private readonly AuthCodeRequestInterface $authCodeRequest,
    ) {
        parent::__construct();
    }

    public function handleOTP(): void
    {
        //todo: catch only OTP exception that will be shown to user, maybe some abstract OTP exception?
        try {
            $this->authService->validate(
                $this->authCodeRequest->getCode()
            );

            //todo: redirect to originally requested page after successful OTP validation
            //todo: create correct session for logged in user (from service, handleLogin?)
        } catch (\Exception $e) {
            //todo: display translated error message to user
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        }
    }

    public function resendCode(): void
    {
        $this->authService->generate();
    }
}
