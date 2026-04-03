<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface
    as OAuthModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettings;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;

abstract class BaseCest
{
    protected function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }

    protected function getNewUserData()
    {
        return Fixtures::get('newUser');
    }

    protected function setPasswordState(bool $state)
    {
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled($state);
    }

    protected function setCaptchaState(bool $state)
    {
        ContainerFacade::get(CaptchaSettingsServiceInterface::class)->saveIsCaptchaEnabled($state);
    }

    protected function setTwoFactorAuthState(bool $state)
    {
        ContainerFacade::get(ModuleSettingServiceInterface::class)->saveBoolean(
            TwoFASettings::ACTIVE,
            $state,
            Module::MODULE_ID
        );
    }

    protected function setProviderState(bool $state)
    {
        $moduleSettings = ContainerFacade::get(OAuthModuleSettingsServiceInterface::class);
        $moduleSettings->saveFacebookEnabled($state);
        $moduleSettings->saveGoogleEnabled($state);
    }
}
