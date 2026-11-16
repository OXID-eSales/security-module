<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\Codeception\Admin\AdminLoginPage;
use OxidEsales\Codeception\Admin\AdminPanel;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 */
final class AdminSessionEvictionCest extends SessionEvictionBaseCest
{
    private const ADMIN_OXID = 'oxdefaultadmin';
    private const ADMIN_LOGIN = 'admin@myoxideshop.com';
    private const ADMIN_PASSWORD = 'admin0303';
    private const ADMIN_PASSWORD_HASH = '6cb4a34e1b66d3445108cd91b67f98b9';
    private const ADMIN_PASSWORD_SALT = '6631386565336161636139613634663766383538633566623662613036636539';

    private string $loginUserField = '#usr';

    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);
        $this->restoreAdminFixture($I);
        $this->setSessionEvictionState(true);
    }

    public function _after(AcceptanceTester $I): void
    {
        parent::_after($I);
        $this->restoreAdminFixture($I);
    }

    public function otherAdminSessionIsLoggedOutAfterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('an admin session is logged out after the admin password changes elsewhere');

        $this->loginToAdmin($I);

        $I->updateInDatabase(
            'oxuser',
            ['OXPASSWORD' => password_hash(uniqid('password'), PASSWORD_BCRYPT)],
            ['OXID' => self::ADMIN_OXID]
        );

        $this->seeAdminLoginForm($I);
    }

    public function adminChangingOwnPasswordIsLoggedOut(AcceptanceTester $I): void
    {
        $I->wantToTest('the admin changing the own password is logged out on the next request');

        $this->loginToAdmin($I)
            ->openUsers()
            ->findByUserName(self::ADMIN_LOGIN)
            ->updatePassword(uniqid('Password1!'));

        $this->seeAdminLoginForm($I);
    }

    private function loginToAdmin(AcceptanceTester $I): AdminPanel
    {
        Translator::switchTranslationDomain(Translator::TRANSLATION_DOMAIN_ADMIN);

        $adminLoginPage = new AdminLoginPage($I);
        $I->amOnPage($adminLoginPage->URL);

        return $adminLoginPage->login(self::ADMIN_LOGIN, self::ADMIN_PASSWORD);
    }

    private function seeAdminLoginForm(AcceptanceTester $I): void
    {
        $I->reloadPage();
        $I->waitForElement($this->loginUserField);
        (new AdminLoginPage($I))->seeLoginForm();
    }

    private function restoreAdminFixture(AcceptanceTester $I): void
    {
        $I->deleteFromDatabase('oxuser', ['OXID' => self::ADMIN_OXID]);
        $I->haveInDatabase('oxuser', [
            'OXID' => self::ADMIN_OXID,
            'OXACTIVE' => 1,
            'OXRIGHTS' => 'malladmin',
            'OXSHOPID' => 1,
            'OXUSERNAME' => self::ADMIN_LOGIN,
            'OXPASSWORD' => self::ADMIN_PASSWORD_HASH,
            'OXPASSSALT' => self::ADMIN_PASSWORD_SALT,
            'OXCUSTNR' => 1,
            'OXFNAME' => 'John',
            'OXLNAME' => 'Doe',
            'OXSTREET' => 'Maple Street',
            'OXSTREETNR' => '2425',
            'OXCITY' => 'Any City',
            'OXCOUNTRYID' => 'testcountry_de',
            'OXZIP' => '9041',
            'OXSAL' => 'MR',
            'OXBONI' => 1000,
            'OXCREATE' => '2003-01-01 00:00:00',
            'OXREGISTER' => '2003-01-01 00:00:00',
        ]);
    }
}
