<?php

declare(strict_types=1);


namespace OxidEsales\SecurityModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE `oxuser` ADD COLUMN `OEEMAILVERIFIED` datetime DEFAULT NULL COMMENT "Email verification timestamp"'
        );

        $this->addSql(
            'CREATE TABLE IF NOT EXISTS `oesm_email_verification` (
                `OXUSERID`     char(32)  NOT NULL                        COMMENT "User ID",
                `TOKEN_HASH`   char(64)  NOT NULL                        COMMENT "SHA-256 hash of the verification token",
                `EXPIRES_AT`   datetime  NOT NULL                        COMMENT "Link expiration timestamp",
                `SEND_COUNT`   int       NOT NULL DEFAULT 1              COMMENT "Verification mails sent",
                `LAST_SENT_AT` datetime  NOT NULL                        COMMENT "Last mail sent timestamp",
                `CREATED_AT`   datetime  NOT NULL                        COMMENT "Verification started timestamp",
                PRIMARY KEY (`OXUSERID`),
                UNIQUE KEY `TOKEN_HASH` (`TOKEN_HASH`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
