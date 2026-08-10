<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\AcceptanceOXAPI;

use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * The account-security toggle
 */
final class SetTwoFactorAuthCest extends BaseCest
{
    private const TOGGLE = 'mutation ($e: Boolean!) { setTwoFactorAuth(enabled: $e) }';

    public function togglesTwoFactorPreferenceForALoggedInUser(AcceptanceTester $I): void
    {
        $this->prepareRegularUser($I);
        $user = $this->user();
        $I->amBearerAuthenticated($this->fullToken($I));

        // Enable
        $enabled = $this->sendGraphQL($I, self::TOGGLE, ['e' => true]);
        $I->assertArrayNotHasKey('errors', $enabled);
        $I->assertTrue($enabled['data']['setTwoFactorAuth'] ?? false);
        $I->seeInDatabase('oxuser', ['OXID' => $user['userId'], 'OE2FAENABLED' => 1]);

        // Disable - same still-valid token; the preference change does not affect the current session.
        $disabled = $this->sendGraphQL($I, self::TOGGLE, ['e' => false]);
        $I->assertArrayNotHasKey('errors', $disabled);
        $I->assertFalse($disabled['data']['setTwoFactorAuth'] ?? true);
        $I->seeInDatabase('oxuser', ['OXID' => $user['userId'], 'OE2FAENABLED' => 0]);
    }

    public function enablingTwoFactorAuthInvalidatesTheCurrentToken(AcceptanceTester $I): void
    {
        $this->prepareRegularUser($I);
        $user = $this->user();
        $I->amBearerAuthenticated($this->fullToken($I));

        $before = $this->sendGraphQL($I, self::TOGGLE, ['e' => false]);
        $I->assertArrayNotHasKey('errors', $before, 'The full token must work before 2FA is enabled');

        $enable = $this->sendGraphQL($I, self::TOGGLE, ['e' => true]);
        $I->assertTrue($enable['data']['setTwoFactorAuth'] ?? false);
        $I->seeInDatabase('oxuser', ['OXID' => $user['userId'], 'OE2FAENABLED' => 1]);

        $after = $this->sendGraphQL($I, self::TOGGLE, ['e' => false]);
        $I->assertArrayHasKey('errors', $after, 'The token must be invalid after 2FA was enabled');
        $I->assertNull($after['data']['setTwoFactorAuth'] ?? null);
        $I->seeInDatabase('oxuser', ['OXID' => $user['userId'], 'OE2FAENABLED' => 1]);
    }

    public function rejectsAnAnonymousChallengeToken(AcceptanceTester $I): void
    {
        $this->prepareTwoFAChallengeUser($I);
        $user = $this->user();

        $I->amBearerAuthenticated($this->challengeToken($I));
        $data = $this->sendGraphQL($I, self::TOGGLE, ['e' => false]);

        $I->assertArrayHasKey('errors', $data, 'An anonymous challenge token must not satisfy #[Logged]');
        $I->assertNull($data['data']['setTwoFactorAuth'] ?? null);
        $I->seeInDatabase('oxuser', ['OXID' => $user['userId'], 'OE2FAENABLED' => 1]);
    }

    public function requiresAuthentication(AcceptanceTester $I): void
    {
        $this->prepareRegularUser($I);
        $I->logout();

        $data = $this->sendGraphQL($I, self::TOGGLE, ['e' => true]);

        $I->assertArrayHasKey('errors', $data, 'An unauthenticated request must be rejected');
        $I->assertNull($data['data']['setTwoFactorAuth'] ?? null);
    }

    private function fullToken(AcceptanceTester $I): string
    {
        $I->logout();
        $user = $this->user();
        $data = $this->sendGraphQL(
            $I,
            'query ($u: String, $p: String) { token(username: $u, password: $p) }',
            ['u' => $user['userLoginName'], 'p' => $user['userPassword']]
        );

        return $data['data']['token'];
    }

    private function challengeToken(AcceptanceTester $I): string
    {
        return $this->fullToken($I);
    }
}
