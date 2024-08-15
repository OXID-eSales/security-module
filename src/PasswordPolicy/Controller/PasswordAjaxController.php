<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Controller;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordValidatorChainInterface;

class PasswordAjaxController extends WidgetController
{
    public function passwordStrength(): void
    {
        $this
            ->getService(PasswordValidatorChainInterface::class)
//            ->validatePassword('!@#$%^&*()_+~/§±abcA1');
            ->validatePassword('§±');
//            ->validatePassword('112');

        var_dump('end');
    }
}
