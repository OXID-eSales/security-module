<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\EmailVerification\DTO\VerificationPageDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationPageServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use OxidEsales\SecurityModule\EmailVerification\Transput\VerificationRequestInterface;

class EmailVerificationController extends FrontendController
{
    /**
     * @var string
     * @SuppressWarnings("PHPMD.CamelCasePropertyName")
     */
    protected $_sThisTemplate = '@oe_security_module/templates/email_verification';

    private ?VerificationPageDtoInterface $verificationPage = null;

    public function __construct(
        private readonly VerificationPageServiceInterface $pageService,
        private readonly EmailVerificationSettingsInterface $settings,
        private readonly VerificationRequestInterface $verificationRequest,
        private readonly Session $session,
    ) {
        parent::__construct();
    }

    public function render(): string
    {
        parent::render();

        $this->addTplParam(
            'verificationPage',
            $this->verificationPage ?? $this->pageService->showPage($this->verificationRequest->isSentStateRequested())
        );
        $this->addTplParam('linkLifetimeHours', $this->settings->getLinkLifetimeHours());

        return $this->_sThisTemplate;
    }

    public function verifyEmail(): void
    {
        $this->verificationPage = $this->pageService->verifyEmail($this->verificationRequest->getToken());
    }

    public function resendEmail(): void
    {
        $token = $this->verificationRequest->getToken();

        $this->verificationPage = $this->session->checkSessionChallenge()
            ? $this->pageService->resendEmail($token)
            : $this->pageService->rejectResendEmail($token);
    }
}
