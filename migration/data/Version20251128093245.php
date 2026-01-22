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
        $this->addSql('ALTER TABLE `oxuser` ADD column `OESMOTPCODE` VARCHAR(128) default NULL COMMENT "OTP code"');
        $this->addSql('ALTER TABLE `oxuser` ADD column `OESMOTPEXPTIME` DATETIME default NULL COMMENT "OTP code expiration time"');
        $this->addSql('ALTER TABLE `oxuser` ADD column `OESMOTPATTEMPTS` INT NOT NULL default 0 COMMENT "OTP code attempts"');
    }

    public function down(Schema $schema): void
    {
    }
}
