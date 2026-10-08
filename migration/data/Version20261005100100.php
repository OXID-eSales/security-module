<?php

declare(strict_types=1);


namespace OxidEsales\SecurityModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005100100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $titleDe = 'Bitte bestätigen Sie Ihre E-Mail-Adresse';
        $bodyDe = <<<'TEXT'
            Hallo {{ userName }},

            vielen Dank für Ihre Registrierung bei {{ shopName }}.

            Bitte bestätigen Sie Ihre E-Mail-Adresse, indem Sie den folgenden Link öffnen:

            {{ verificationLink }}

            Der Link ist gültig bis {{ expiresAt }} und kann nur einmal verwendet werden. Erst nach der Bestätigung können Sie sich in Ihrem Kundenkonto anmelden.

            Falls Sie sich nicht registriert haben, können Sie diese E-Mail ignorieren.

            Ihr {{ shopName }}-Team.
            TEXT;

        $titleEn = 'Please confirm your email address';
        $bodyEn = <<<'TEXT'
            Hello {{ userName }},

            Thank you for registering at {{ shopName }}.

            Please confirm your email address by opening the following link:

            {{ verificationLink }}

            The link is valid until {{ expiresAt }} and can only be used once. You can log in to your account only after confirming your address.

            If you didn't register, you can safely ignore this email.

            Your {{ shopName }} team.
            TEXT;

        $this->addSql("DELETE FROM `oxcontents` WHERE `OXLOADID` = 'oesmemailverification'");
        $this->addSql(
            "INSERT INTO `oxcontents`
                (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXFOLDER`,
                 `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXCONTENT_2`, `OXCONTENT_3`)
             VALUES
                (MD5('oesmemailverification'), 'oesmemailverification', 1, 1, 0, 1, 1, 'CMSFOLDER_EMAILS',
                 ?, ?, ?, ?, '', '')",
            [$titleDe, $bodyDe, $titleEn, $bodyEn]
        );
    }

    public function down(Schema $schema): void
    {
    }
}
