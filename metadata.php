<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'oe_security_module',
    'title'       => 'Security module',
    'description' => 'Security module',
    'thumbnail'   => 'logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'https://github.com/OXID-eSales/security-module',
    'email'       => 'info@oxid-esales.com',
    'extend' => [
    ],
    'controllers' => [
        'password'  => \OxidEsales\SecurityModule\PasswordPolicy\Controller\PasswordAjaxController::class
    ],
    'templates' => [
    ],
    'events' => [
    ],
    'blocks' => [
    ],
    'settings' => [
    ],
];
