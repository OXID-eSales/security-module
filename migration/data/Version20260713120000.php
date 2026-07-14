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
        // The body is stored as plain text with newlines: the plain email template renders it as-is,
        // the HTML template wraps it in {% apply nl2br %} so the same newlines become <br>. {{ otp }}
        // is substituted at render time. The "5 minutes" wording tracks the hardwired OTP lifetime
        // in OtpChallengeStateService (todo there: move to a setting and keep this copy in sync).
        $titleDe = 'Ihr OXID eShop Verifizierungscode';
        $bodyDe = <<<'TEXT'
            Hallo,

            jemand (hoffentlich Sie) versucht, sich in Ihrem OXID eShop-Konto anzumelden.

            Ihr Verifizierungscode lautet: {{ otp }}

            Dieser Code läuft in 5 Minuten ab und kann nur einmal verwendet werden.

            Falls Sie sich nicht anmelden wollten, können Sie diese E-Mail ignorieren – zur Sicherheit empfehlen wir jedoch, Ihr Passwort zu ändern.

            Ihr OXID eShop-Team.
            TEXT;

        $titleEn = 'Your OXID eShop verification code';
        $bodyEn = <<<'TEXT'
            Hello,

            Someone (hopefully you) is trying to log in to your OXID eShop account.

            Your verification code is: {{ otp }}

            This code expires in 5 minutes and can only be used once.

            If you didn't try to log in, you can safely ignore this email — but we'd recommend changing your password just to be safe.

            Your OXID eShop team.
            TEXT;

        $this->addSql("DELETE FROM `oxcontents` WHERE `OXLOADID` = 'oesm2faotpemail'");
        $this->addSql(
            "INSERT INTO `oxcontents`
                (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXFOLDER`,
                 `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXCONTENT_2`, `OXCONTENT_3`)
             VALUES
                (MD5('oesm2faotpemail'), 'oesm2faotpemail', 1, 1, 0, 1, 1, 'CMSFOLDER_EMAILS',
                 ?, ?, ?, ?, '', '')",
            [$titleDe, $bodyDe, $titleEn, $bodyEn]
        );
    }

    public function down(Schema $schema): void
    {
    }
}
