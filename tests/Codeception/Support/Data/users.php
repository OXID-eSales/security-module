<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

return [
    'existingUser' => [
        'userId'        => 'testuser',
        'userLoginName' => 'some_test_user@oxid-esales.dev',
        'userPassword'  => 'useruser',
        'userName'      => 'UserNamešÄßüл',
        'userLastName'  => 'UserSurnamešÄßüл',
    ],
    'newUser' => [
        'inputFields' => [
            'userLoginName'              => 'new_test_user@oxid-esales.dev',
            'userPassword'               => 'useruser',
            'userPasswordConfirm'        => 'useruser',
            'invadr[oxuser__oxfname]'    => 'New',
            'invadr[oxuser__oxlname]'    => 'User',
            'invadr[oxuser__oxstreet]'   => 'Street',
            'invadr[oxuser__oxstreetnr]' => '55',
            'invadr[oxuser__oxzip]'      => '5555',
            'invadr[oxuser__oxcity]'     => 'City',
        ],
        'selectFields' => [
            'invCountrySelect' => 'a7c40f631fc920687.20179984'
        ]
    ]
];
