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
use OxidEsales\SecurityModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * Feature on: the admin backend uses the 'auth' session variable and a login-token
 * bound to the admin password hash (FR009). An admin backend session whose token is
 * for the old password is evicted on its next request, including the admin session
 * that performs the change itself.
 *
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 * @group flow:admin-backend-session-eviction
 */
class AdminSessionEvictionCest extends BaseCest
{
    private const ADMIN_OXID = 'oxdefaultadmin';
    private const ADMIN_LOGIN = 'admin@myoxideshop.com';
    private const ADMIN_PASSWORD = 'admin0303';
    private const ADMIN_PASSWORD_HASH = '6cb4a34e1b66d3445108cd91b67f98b9';
    private const ADMIN_PASSWORD_SALT = '6631386565336161636139613634663766383538633566623662613036636539';

    private string $loginUserFieldId = '#usr';

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setHoneyPotCaptchaState(false);
        $this->setPasswordState(false);
        $this->setSessionEvictionState(true);
        $this->restoreAdminFixture($I);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->setSessionEvictionState(false);
        $this->restoreAdminFixture($I);
    }

    private function restoreAdminFixture(AcceptanceTester $I): void
    {
        // The Db module keeps changes between tests (cleanup: false) and the acting-session
        // test changes this admin's password. Rebuild the admin fixture to a known state
        // before AND after each test, so a changed password never leaks into a later test
        // or a later Cest.
        $I->deleteFromDatabase('oxuser', ['OXID' => self::ADMIN_OXID]);
        $I->haveInDatabase('oxuser', $this->adminFixtureRow());
    }

    public function testOtherAdminBackendSessionEvictedAfterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('an admin backend session is logged out on its next request after the password changes');

        $this->loginToAdminBackend($I);

        // The admin password is changed elsewhere (updated directly in the database), so this
        // session's login-token becomes stale.
        $I->updateInDatabase(
            'oxuser',
            ['OXPASSWORD' => password_hash('ChangedElsewhere987!', PASSWORD_BCRYPT)],
            ['OXID' => self::ADMIN_OXID]
        );

        // The next backend request must evict this session (stale login-token) and bounce it
        // to the admin login form.
        $I->reloadPage();
        $I->waitForElement($this->loginUserFieldId);
        (new AdminLoginPage($I))->seeLoginForm();
    }

    public function testActingAdminSessionEvictedAfterOwnPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('the admin changing its own password through the backend is logged out on the next request');

        $adminPanel = $this->loginToAdminBackend($I);

        $adminPanel
            ->openUsers()
            ->findByUserName(self::ADMIN_LOGIN)
            ->updatePassword('NewAdminPass123!');

        $I->reloadPage();
        $I->waitForElement($this->loginUserFieldId);
        (new AdminLoginPage($I))->seeLoginForm();
    }

    private function loginToAdminBackend(AcceptanceTester $I): AdminPanel
    {
        Translator::switchTranslationDomain(Translator::TRANSLATION_DOMAIN_ADMIN);

        $adminLoginPage = new AdminLoginPage($I);
        $I->amOnPage($adminLoginPage->URL);

        return $adminLoginPage->login(self::ADMIN_LOGIN, self::ADMIN_PASSWORD);
    }

    private function adminFixtureRow(): array
    {
        return [
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
        ];
    }
}
