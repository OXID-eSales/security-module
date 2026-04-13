<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\CodeValidationException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAResendableInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
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
        private readonly TwoFAServiceInterface $twoFAService,
        private readonly TwoFAUserServiceInterface $twoFAUserService,
        private readonly AuthCodeRequestInterface $authCodeRequest,
        private readonly UtilsView $utilsView,
        private readonly JsonResponseInterface $jsonResponse,
    ) {
        parent::__construct();
    }

    public function render(): string
    {
        parent::render();

        $isResendable = $this->twoFAService instanceof TwoFAResendableInterface;
        $this->addTplParam('resendable', $isResendable);

        if ($isResendable) {
            $userId = $this->twoFAUserService->getPendingUserId();
            $this->addTplParam('remainingAttempts', $this->twoFAService->getRemainingAttempts($userId));
            $this->addTplParam('resendCooldownRemaining', $this->twoFAService->getCooldownRemaining($userId));
        }

        return $this->_sThisTemplate;
    }

    public function handleOTP(): ?string
    {
        $userId = $this->twoFAUserService->getPendingUserId();
        $code = $this->authCodeRequest->getCode();

        try {
            $this->twoFAService->verify($userId, $code);
            $this->twoFAUserService->loginUser($userId);
        } catch (CodeValidationException $e) {
            $this->utilsView->addErrorToDisplay($e);
        }

        return null;
    }

    public function resendCode(): void
    {
        if (!$this->twoFAService instanceof TwoFAResendableInterface) {
            $this->jsonResponse->send(['success' => false], 405);
            return;
        }

        $userId = $this->twoFAUserService->getPendingUserId();
        try {
            $this->twoFAService->resend($userId);
            $this->jsonResponse->send([
                'success' => true,
                'remainingAttempts' => $this->twoFAService->getRemainingAttempts($userId),
            ]);
        } catch (ResendCooldownException) {
            $this->jsonResponse->send(['success' => false], 429);
        }
    }
}
