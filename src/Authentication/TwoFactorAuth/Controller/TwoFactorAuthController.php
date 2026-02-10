<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\OTPValidationException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\JsonResponseInterface;

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
        private readonly UserServiceInterface $userService,
        private readonly AuthCodeRequestInterface $authCodeRequest,
        private readonly UtilsView $utilsView,
        private readonly JsonResponseInterface $jsonResponse,
    ) {
        parent::__construct();
    }

    public function handleOTP(): ?string
    {
        try {
            $this->authService->validate(
                $this->authCodeRequest->getCode()
            );

            $this->userService->finalizeLogin();
        } catch (OTPValidationException $e) {
            $this->utilsView->addErrorToDisplay($e);
        }

        return null;
    }

    public function resendCode(): void
    {
        $success = $this->authService->resend();

        if (!$success) {
            $this->jsonResponse->setStatusCode(429);
        }

        $this->jsonResponse->send(['success' => $success]);
    }
}
