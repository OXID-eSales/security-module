<?php

declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seeds the CMS content for the 2FA OTP email.
 *
 * The OXLOADID literal below MUST equal
 * OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailContent::IDENT.
 * A migration is an immutable historical artifact and must not depend on module classes, hence the literal.
 */
final class Version20260713120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM `oxcontents` WHERE `OXLOADID` = 'oesm2faotpemail'");
        $this->addSql(
            "INSERT INTO `oxcontents`
                (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXFOLDER`,
                 `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXCONTENT_2`, `OXCONTENT_3`)
             VALUES
                (MD5('oesm2faotpemail'), 'oesm2faotpemail', 1, 1, 0, 1, 1, 'CMSFOLDER_EMAILS',
                 'Ihr Verifizierungscode', 'Ihr Verifizierungscode lautet: {{ otp }}',
                 'Your verification code', 'Your verification code is: {{ otp }}', '', '')"
        );
    }

    public function down(Schema $schema): void
    {
    }
}
