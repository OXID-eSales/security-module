<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Shop\Core;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateExceptionInterface;
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
     */
    public function checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength = false)
    {
        $passwordValidator = $this->getService(PasswordValidatorChainInterface::class);

        try {
            $passwordValidator->validatePassword($newPassword);
        } catch (PasswordValidateExceptionInterface $e) {
            $exception = oxNew(InputException::class);
            $exception->setMessage($e->getMessage());

            return $this->addValidationError("oxuser__oxpassword", $exception);
        }

        return parent::checkPassword($user, $newPassword, $confirmationPassword, $shouldCheckPasswordLength);
    }
}
