<?php

declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251128093245 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `oxuser` ADD column `OE2FAENABLED` TINYINT(1) NOT NULL default 0 COMMENT "User has 2FA enabled"');
    }

    public function down(Schema $schema): void
    {
    }
}
