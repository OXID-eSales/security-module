<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;

abstract class BaseCest
{
    protected function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }

    protected function setPasswordState(bool $state)
    {
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled($state);
    }

    protected function setCaptchaState(bool $state)
    {
        ContainerFacade::get(CaptchaSettingsServiceInterface::class)->saveIsCaptchaEnabled($state);
    }
}
