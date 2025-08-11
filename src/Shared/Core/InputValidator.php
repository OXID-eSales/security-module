<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\PasswordPolicy\Intrastructure\ExceptionFactoryInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordCollectionException;
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
        if ($newPassword === null || !$settingsService->isPasswordPolicyEnabled()) {
            return parent::checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength);
        }

        $passwordValidator = $this->getService(PasswordValidatorChainInterface::class);

        try {
            $passwordValidator->validatePassword($newPassword);
        } catch (PasswordCollectionException $e) {
            $exceptionFactory = $this->getService(ExceptionFactoryInterface::class);
            foreach ($e->getValidationExceptions() as $key => $error) {
                if ($key === count($e->getValidationExceptions()) - 1) {
                    return $this->addValidationError(
                        "oxuser__oxpassword",
                        $exceptionFactory->create($error)
                    );
                }

                Registry::getUtilsView()->addErrorToDisplay($exceptionFactory->create($error));
            }
        }

        return parent::checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength);
    }
}
