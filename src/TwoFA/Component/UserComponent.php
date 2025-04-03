<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Component;

use OxidEsales\Eshop\Core\Registry;

class UserComponent extends UserComponent_parent
{
    public function registerUser()
    {
        $registration = parent::registerUser();

        if ($registration) {
            Registry::getUtils()->redirect(
                Registry::getConfig()->getShopHomeUrl() . 'cl=2faregister&success=1'
            );
        }
    }
}
