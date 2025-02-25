<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Core;

use OxidEsales\SecurityModule\PasswordPolicy\Intrastructure\ExceptionFactoryInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordValidatorChainInterface;

/**
 * Class InputValidator
 *
 * @mixin \OxidEsales\Eshop\Core\InputValidator
 */
class InputValidator extends InputValidator_parent
{
    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength = false)
    {
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isPasswordPolicyEnabled()) {
            parent::checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength);
        }

        $passwordValidator = $this->getService(PasswordValidatorChainInterface::class);

        try {
            $passwordValidator->validatePassword($newPassword);
        } catch (PasswordValidateException $e) {
            $exceptionFactory = $this->getService(ExceptionFactoryInterface::class);

            return $this->addValidationError(
                "oxuser__oxpassword",
                $exceptionFactory->create($e)
            );
        }

        return parent::checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength);
    }
}
