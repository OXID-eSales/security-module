<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsService as TwoFactorAuthModuleSettings;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsService as PasswordPolicyModuleSettings;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsService as CaptchaModuleSettings;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsService as OAuthModuleSettings;
use OxidEsales\SecurityModule\Core\Module;

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => Module::MODULE_ID,
    'title'       => 'OXID Security Module',
    'description' => [
        'en' => 'Tools to protect your shop and safeguard customer accounts.',
        'de' => 'Werkzeuge zum Schutz Ihres Shops und zur Sicherung von Kundenkonten.'
    ],
    'thumbnail'   => 'logo.png',
    'version'     => '2.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'https://github.com/OXID-eSales/security-module',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        \OxidEsales\Eshop\Application\Controller\NewsletterController::class => \OxidEsales\SecurityModule\Captcha\Shop\NewsletterController::class,
        \OxidEsales\Eshop\Application\Controller\ForgotPasswordController::class => \OxidEsales\SecurityModule\Captcha\Shop\ForgotPasswordController::class,
        \OxidEsales\Eshop\Application\Model\User::class => \OxidEsales\SecurityModule\Shared\Model\User::class,
        \OxidEsales\Eshop\Core\InputValidator::class    => \OxidEsales\SecurityModule\Shared\Core\InputValidator::class,
        \OxidEsales\Eshop\Core\ViewConfig::class        => \OxidEsales\SecurityModule\Shared\Core\ViewConfig::class
    ],
    'controllers' => [
        'captcha' => \OxidEsales\SecurityModule\Captcha\Controller\CaptchaController::class,
        'password' => \OxidEsales\SecurityModule\PasswordPolicy\Controller\PasswordAjaxController::class,
        'oauth' => \OxidEsales\SecurityModule\Authentication\OAuth2\Controller\OAuthController::class,
    ],
    'templates'   => [
    ],
    'events'      => [
    ],
    'blocks'      => [
    ],
    'settings'    => [
        //Password policy enable
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_POLICY_ENABLE,
            'type'  => 'bool',
            'value' => true
        ],

        //Password length requirements
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_MINIMUM_LENGTH,
            'type'  => 'num',
            'value' => 8
        ],

        //Password symbols requirements
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_UPPERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_LOWERCASE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_DIGIT,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'password_policy',
            'name'  => PasswordPolicyModuleSettings::PASSWORD_SPECIAL_CHAR,
            'type'  => 'bool',
            'value' => true
        ],

        //Captcha
        [
            'group' => 'captcha',
            'name'  => CaptchaModuleSettings::CAPTCHA_ENABLE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'captcha',
            'name'  => CaptchaModuleSettings::HONEYPOT_CAPTCHA_ENABLE,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'captcha',
            'name'  => CaptchaModuleSettings::CAPTCHA_LIFETIME,
            'type'  => 'select',
            'constraints' => '5min|15min|30min',
            'value' => '15min'
        ],

        //OAuth2 settings
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::FACEBOOK_LOGIN_ENABLED,
            'type'  => 'bool',
            'value' => false
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::FACEBOOK_CLIENT_ID,
            'type'  => 'str',
            'value' => ''
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::FACEBOOK_CLIENT_SECRET,
            'type'  => 'str',
            'value' => ''
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::FACEBOOK_REDIRECT_URL,
            'type'  => 'str',
            'value' => ''
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::GOOGLE_LOGIN_ENABLED,
            'type'  => 'bool',
            'value' => true
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::GOOGLE_CLIENT_ID,
            'type'  => 'str',
            'value' => ''
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::GOOGLE_CLIENT_SECRET,
            'type'  => 'str',
            'value' => ''
        ],
        [
            'group' => 'oauth',
            'name'  => OAuthModuleSettings::GOOGLE_REDIRECT_URL,
            'type'  => 'str',
            'value' => ''
        ],

        //TwoFactorAuth settings
        [
            'group' => 'two_factor_auth',
            'name'  => TwoFactorAuthModuleSettings::ACTIVE,
            'type'  => 'bool',
            'value' => false
        ],
        [
            'group' => 'two_factor_auth',
            'name'  => TwoFactorAuthModuleSettings::TWO_FACTOR_TYPE,
            'type'  => 'select',
            'constraints' => 'otp|totp',
            'value' => 'otp'
        ],
    ],
];
