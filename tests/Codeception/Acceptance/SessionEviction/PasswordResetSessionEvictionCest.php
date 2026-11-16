<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettings;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 */
final class PasswordResetSessionEvictionCest extends SessionEvictionBaseCest
{
    private string $newPasswordField = '#password_new';
    private string $confirmPasswordField = '#password_new_confirm';
    private string $saveButton = '//form[@name="forgotpwd"]//button[@type="submit"]';

    public function _after(AcceptanceTester $I): void
    {
        $this->setEmailVerificationState(false);
        $I->deleteFromDatabase('oesm_email_verification', ['OXUSERID' => $this->getExistingUserData()['userId']]);
        $I->updateInDatabase(
            'oxuser',
            ['OEEMAILVERIFIED' => null],
            ['OXID' => $this->getExistingUserData()['userId']]
        );
        parent::_after($I);
    }

    public function resetUserStaysLoggedInWithStrictMode(AcceptanceTester $I): void
    {
        $I->wantToTest('a user who resets the password stays logged in with strict mode');
        $this->setSessionEvictionState(true, true);

        $this->resetPassword($I);

        $this->seeLoggedIn($I);
        $this->seeLoggedIn($I);
    }

    public function resetSessionIsLoggedOutAfterLaterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('a session opened by a password reset is logged out after a later password change');
        $this->setSessionEvictionState(true);

        $this->resetPassword($I);
        $this->seeLoggedIn($I);

        $this->changePasswordElsewhere($I);

        $this->seeLoggedOut($I);
    }

    public function unverifiedUserIsVerifiedAndStaysLoggedInWithStrictMode(AcceptanceTester $I): void
    {
        $I->wantToTest('an unverified user who resets the password is verified and stays logged in with strict mode');
        $userId = $this->getExistingUserData()['userId'];
        $this->setEmailVerificationState(true);
        $I->haveInDatabase('oesm_email_verification', [
            'OXUSERID' => $userId,
            'TOKEN_HASH' => hash('sha256', uniqid('token')),
            'EXPIRES_AT' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'SEND_COUNT' => 1,
            'LAST_SENT_AT' => date('Y-m-d H:i:s'),
            'CREATED_AT' => date('Y-m-d H:i:s'),
        ]);
        $this->setSessionEvictionState(true, true);

        $this->resetPassword($I);

        $this->seeLoggedIn($I);
        $I->dontSeeInDatabase('oesm_email_verification', ['OXUSERID' => $userId]);
        $I->dontSeeInDatabase('oxuser', ['OXID' => $userId, 'OEEMAILVERIFIED' => null]);
    }

    private function resetPassword(AcceptanceTester $I): void
    {
        $password = uniqid('Password1!');

        $I->amOnPage('/index.php?cl=forgotpwd&uid=' . $this->createUpdateId($I));
        $I->waitForElementVisible($this->newPasswordField);
        $I->retryFillField($this->newPasswordField, $password);
        $I->retryFillField($this->confirmPasswordField, $password);
        $I->retryClick($this->saveButton);
        $I->waitForPageLoad();
    }

    private function setEmailVerificationState(bool $state): void
    {
        ContainerFacade::get(ModuleSettingServiceInterface::class)
            ->saveBoolean(EmailVerificationSettings::ACTIVE, $state, Module::MODULE_ID);
    }
}
