<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Component;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFactorRequiredException;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailNotVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;

/**
 * @mixin \OxidEsales\Eshop\Application\Component\UserComponent
 * @eshopExtension
 */
class UserComponent extends UserComponent_parent
{
    private const EMAIL_VERIFICATION_SENT_ACTION = 'oesm_email_verification?state=sent';

    private bool $verificationStarted = false;

    public function login()
    {
        if ($this->isCaptchaEnabled()) {
            try {
                $this->getService(CaptchaServiceInterface::class)
                    ->validate($this->getService(Request::class));
            } catch (StandardException $e) {
                $this->getService(UtilsView::class)->addErrorToDisplay($e->getMessage());
                $this->setLoginStatus(USER_LOGIN_FAIL);
                return 'user';
            }
        }

        try {
            return parent::login();
        } catch (TwoFactorRequiredException $e) {
            $this->getService(Utils::class)->redirect($e->getVerificationUrl());
            return 'user';
        } catch (EmailNotVerifiedException $e) {
            $this->getService(Utils::class)->redirect($e->getVerificationUrl());
            return 'user';
        }
    }

    public function createUser()
    {
        if ($this->isCaptchaEnabled()) {
            try {
                $this->getService(CaptchaServiceInterface::class)
                    ->validate($this->getService(Request::class));
            } catch (StandardException $e) {
                $this->getService(UtilsView::class)->addErrorToDisplay($e->getMessage());
                return false;
            }
        }

        $result = parent::createUser();

        return $this->stopAtEmailVerification($result) ? self::EMAIL_VERIFICATION_SENT_ACTION : $result;
    }

    public function registerUser()
    {
        $result = parent::registerUser();

        return $this->verificationStarted ? self::EMAIL_VERIFICATION_SENT_ACTION : $result;
    }

    private function stopAtEmailVerification(mixed $createUserResult): bool
    {
        $userId = $this->getService(SessionInterface::class)->get('usr');
        if ($createUserResult === false || !$this->_blIsNewUser || !is_string($userId) || $userId === '') {
            return false;
        }

        if (!$this->getService(UserEmailVerificationFacadeInterface::class)->isEmailVerificationRequired($userId)) {
            return false;
        }

        $this->getService(UserModelFactoryInterface::class)->create()->logout();
        $this->getService(UserEmailVerificationFacadeInterface::class)->rememberPendingUser($userId);
        $this->verificationStarted = true;

        return true;
    }

    private function isCaptchaEnabled(): bool
    {
        $settings = $this->getService(ModuleSettingsServiceInterface::class);
        return $settings->isCaptchaEnabled() || $settings->isHoneyPotCaptchaEnabled();
    }
}
