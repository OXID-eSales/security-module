<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\UserSettingsUpdateRequestInterface;

class AccountSecurityController extends AccountController
{
    public function __construct(
        private readonly TwoFAUserSettingsInterface $userSettingsService,
        private readonly UserSettingsUpdateRequestInterface $settingUpdateRequest,
    ) {
        $this->setTemplateName('@oe_security_module/templates/account_security');
        parent::__construct();
    }

    public function render(): string
    {
        $parentResult = parent::render();

        $user = $this->getUser();
        if ($user) {
            $this->addTplParam('twoFAEnabledForUser', $this->userSettingsService->isEnabledForUser($user->getId()));
        }

        return $parentResult;
    }

    public function saveTwoFactorAuth(): void
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $this->userSettingsService->setEnabledForUser(
            $user->getId(),
            $this->settingUpdateRequest->isTwoFAEnabled()
        );

        $this->addTplParam('twoFASaved', true);
    }
}
