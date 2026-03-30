<?php

declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330141320 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS `oesm_2fa_otp` (
                `OXUSERID`     char(32)     NOT NULL                        COMMENT "User ID",
                `CODE_HASH`    varchar(255) NOT NULL                        COMMENT "Hashed OTP code",
                `ATTEMPTS`     int          NOT NULL DEFAULT 0              COMMENT "Failed verification attempts",
                `LAST_SENT_AT` datetime     DEFAULT NULL                    COMMENT "Last code sent timestamp",
                `EXPIRES_AT`   datetime     NOT NULL                        COMMENT "Code expiration timestamp",
                `VERIFIED_AT`  datetime     DEFAULT NULL                    COMMENT "Verification timestamp",
                PRIMARY KEY (`OXUSERID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
