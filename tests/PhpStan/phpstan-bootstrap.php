<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

class_alias(
    \OxidEsales\Eshop\Core\InputValidator::class,
    \OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\InputValidator_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\ViewConfig::class,
    \OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\ViewConfig_parent::class
);
