<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Core\Module;

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => Module::MODULE_ID,
    'title'       => 'OXID Security Module',
    'description' => '',
    'thumbnail'   => 'logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'https://github.com/OXID-eSales/security-module',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        \OxidEsales\Eshop\Core\InputValidator::class => \OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\InputValidator::class,
        \OxidEsales\Eshop\Core\ViewConfig::class     => \OxidEsales\SecurityModule\PasswordPolicy\Shop\Core\ViewConfig::class
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
            'group' => 'password_policy',
            'name'  => ModuleSettingsService::PASSWORD_MINIMUM_LENGTH,
            'type'  => 'num',
            'value' => 8
        ],

        //Password symbols requirements
        [
            'group' => 'password_policy',
            'name'  => ModuleSettingsService::PASSWORD_UPPERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => ModuleSettingsService::PASSWORD_LOWERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => ModuleSettingsService::PASSWORD_DIGIT,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => ModuleSettingsService::PASSWORD_SPECIAL_CHAR,
            'type'  => 'bool',
            'value' => true
        ],
    ],
];
