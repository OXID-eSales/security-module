<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Controller;

use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionServiceInterface;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\RegisterController
 * @eshopExtension
 */
class RegisterController extends RegisterController_parent
{
    private const CONFIRM_SUCCESS_ACTION = 'register?confirmstate=1';

    public function confirmRegistration()
    {
        $result = parent::confirmRegistration();

        if ($result === self::CONFIRM_SUCCESS_ACTION) {
            $this->getService(SessionEvictionServiceInterface::class)->bindLoginTokenToSessionUser();
        }

        return $result;
    }
}
