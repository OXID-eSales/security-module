<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSetting;
use OxidEsales\SecurityModule\Core\Module;

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => Module::MODULE_ID,
    'title'       => 'Security module',
    'description' => 'Security module',
    'thumbnail'   => 'logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'https://github.com/OXID-eSales/security-module',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        \OxidEsales\Eshop\Core\InputValidator::class => \OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\InputValidator::class
    ],
    'controllers' => [
        'password' => \OxidEsales\SecurityModule\PasswordPolicy\Controller\PasswordAjaxController::class
    ],
    'templates'   => [
    ],
    'events'      => [
    ],
    'blocks'      => [
    ],
    'settings'    => [
        //Password length requirements
        [
            'group' => 'password',
            'name'  => ModuleSetting::PASSWORD_MINIMUM_LENGTH,
            'type'  => 'num',
            'value' => 8
        ],

        //Password symbols requirements
        [
            'group' => 'passwordpolicy_requirements',
            'name'  => ModuleSetting::PASSWORD_UPPERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'passwordpolicy_requirements',
            'name'  => ModuleSetting::PASSWORD_LOWERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'passwordpolicy_requirements',
            'name'  => ModuleSetting::PASSWORD_DIGIT,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'passwordpolicy_requirements',
            'name'  => ModuleSetting::PASSWORD_SPECIAL_CHAR,
            'type'  => 'bool',
            'value' => true
        ],
    ],
];
