<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\Infrastructure\Provider\Factory;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Factory\EmailFactory;
use PHPUnit\Framework\TestCase;

class EmailFactoryTest extends TestCase
{
    public function testCreateUserFactory(): void
    {
        $emailFactory = new EmailFactory();
        $emailModel = $emailFactory->create();

        $this->assertInstanceOf(Email::class, $emailModel);
    }

    public function testCreateMultipleUserFactory(): void
    {
        $emailFactory = new EmailFactory();

        $emailModel = $emailFactory->create();
        $this->assertInstanceOf(Email::class, $emailModel);

        $newEmailFactory = $emailFactory->create();
        $this->assertInstanceOf(Email::class, $newEmailFactory);
        $this->assertNotSame($emailModel, $newEmailFactory);
    }
}
