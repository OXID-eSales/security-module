<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

abstract class SessionEvictionBaseCest extends BaseCest
{
    protected const ACCOUNT_URL = '/en/my-account/';
    protected const REMEMBER_ME_COOKIE = 'oxid_1';
    protected const SESSION_COOKIE = 'sid';

    private const SESSION_KEY_COOKIE = 'sid_key';
    private const SHOP_ID = 1;
    private const FIXTURE_PASSWORD_HASH = 'c9dadd994241c9e5fa6469547009328a';
    private const FIXTURE_PASSWORD_SALT = '7573657275736572';

    private string $loginUserField = '#loginUser';
    private string $loginPasswordField = '#loginPwd';
    private string $rememberMeCheckbox = '#lgn_cook';
    private string $accountLoginButton = '#loginButton';

    private bool $originalRememberMe = false;

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setHoneyPotCaptchaState(false);
        $this->setPasswordState(false);
        $this->setSessionEvictionState(false);
        $this->restoreFixtureUser($I);

        $config = Registry::getConfig();
        $this->originalRememberMe = (bool) $config->getConfigParam('blShowRememberMe');
        $config->saveShopConfVar('bool', 'blShowRememberMe', true);

        $I->openShop();
        $this->dropAllSessionCookies($I);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->setSessionEvictionState(false);
        Registry::getConfig()->saveShopConfVar('bool', 'blShowRememberMe', $this->originalRememberMe);
        $this->restoreFixtureUser($I);
    }

    protected function login(AcceptanceTester $I, bool $rememberMe = false): void
    {
        $userData = $this->getExistingUserData();

        $I->amOnPage(self::ACCOUNT_URL);
        $I->waitForElementVisible($this->loginUserField);
        $I->retryFillField($this->loginUserField, $userData['userLoginName']);
        $I->retryFillField($this->loginPasswordField, $userData['userPassword']);
        if ($rememberMe) {
            $I->checkOption($this->rememberMeCheckbox);
        }
        $I->retryClick($this->accountLoginButton);
        $I->waitForPageLoad();
    }

    protected function loginWithRememberMeCookieOnly(AcceptanceTester $I): void
    {
        $this->login($I, true);
        $I->retrySeeCookie(self::REMEMBER_ME_COOKIE);

        $I->resetCookie(self::SESSION_COOKIE);
        $I->resetCookie(self::SESSION_KEY_COOKIE);
        $I->amOnPage(self::ACCOUNT_URL);
    }

    protected function dropAllSessionCookies(AcceptanceTester $I): void
    {
        $I->resetCookie(self::REMEMBER_ME_COOKIE);
        $I->resetCookie(self::SESSION_COOKIE);
        $I->resetCookie(self::SESSION_KEY_COOKIE);
    }

    protected function changePasswordElsewhere(AcceptanceTester $I): void
    {
        $I->updateInDatabase(
            'oxuser',
            ['OXPASSWORD' => password_hash(uniqid('password'), PASSWORD_BCRYPT)],
            ['OXID' => $this->getExistingUserData()['userId']]
        );
    }

    protected function createUpdateId(AcceptanceTester $I): string
    {
        $userId = $this->getExistingUserData()['userId'];
        $updateKey = uniqid('updateKey');

        $I->updateInDatabase(
            'oxuser',
            ['OXUPDATEKEY' => $updateKey, 'OXUPDATEEXP' => time() + 3600],
            ['OXID' => $userId]
        );

        return md5($userId . self::SHOP_ID . $updateKey);
    }

    protected function seeLoggedIn(AcceptanceTester $I): void
    {
        $I->amOnPage(self::ACCOUNT_URL);
        $I->see(Translator::translate('HELLO') . ' ' . $this->getExistingUserData()['userName']);
        $I->dontSeeElement($this->accountLoginButton);
    }

    protected function seeLoggedOut(AcceptanceTester $I): void
    {
        $I->amOnPage(self::ACCOUNT_URL);
        $I->seeElement($this->accountLoginButton);
        $I->dontSee(Translator::translate('HELLO') . ' ' . $this->getExistingUserData()['userName']);
    }

    private function restoreFixtureUser(AcceptanceTester $I): void
    {
        $I->updateInDatabase(
            'oxuser',
            [
                'OXPASSWORD' => self::FIXTURE_PASSWORD_HASH,
                'OXPASSSALT' => self::FIXTURE_PASSWORD_SALT,
                'OXUPDATEKEY' => '',
                'OXUPDATEEXP' => 0,
            ],
            ['OXID' => $this->getExistingUserData()['userId']]
        );
    }
}
