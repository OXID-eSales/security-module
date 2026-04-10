<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;

class AccountSecurityController extends FrontendController
{
    /**
     * @var string
     * @SuppressWarnings("PHPMD.CamelCasePropertyName")
     */
    protected $_sThisTemplate = '@oe_security_module/templates/account_security';

    public function __construct(
        private readonly TwoFAUserSettingsInterface $userSettingsService,
    ) {
        parent::__construct();
    }

    public function saveTwoFactorAuth(): void
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $enabled = (bool) Registry::getRequest()->getRequestParameter('twofa_enabled');
        $this->userSettingsService->setEnabledForUser($user->getId(), $enabled);
    }

    public function isTwoFAEnabled(): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        return $this->userSettingsService->isEnabledForUser($user->getId());
    }
}
