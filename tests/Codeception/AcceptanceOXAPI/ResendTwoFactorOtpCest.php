<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\AcceptanceOXAPI;

use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * The resend flow: token() -> mfa_pending challenge token -> resendTwoFactorOtp() issues a fresh OTP,
 * bounded by the OtpSendPolicyService 60s cooldown and only for a valid challenge token.
 */
final class ResendTwoFactorOtpCest extends BaseCest
{
    private const RESEND = 'mutation { resendTwoFactorOtp }';
    private const COOLDOWN_MESSAGE = 'Please wait before requesting a new two-factor code';

    public function resendWithinCooldownIsRejectedWithACleanError(AcceptanceTester $I): void
    {
        $this->prepareTwoFAChallengeUser($I);

        $challenge = $this->requestChallengeToken($I);
        $I->amBearerAuthenticated($challenge);

        $data = $this->sendGraphQL($I, self::RESEND);

        $I->assertArrayHasKey('errors', $data, 'A resend inside the cooldown must error');
        $I->assertNull($data['data']['resendTwoFactorOtp'] ?? null);
        $I->assertSame(self::COOLDOWN_MESSAGE, $data['errors'][0]['message'] ?? '');
    }

    public function resendAfterCooldownSendsAFreshOtp(AcceptanceTester $I): void
    {
        $this->prepareTwoFAChallengeUser($I);
        $user = $this->user();

        $challenge = $this->requestChallengeToken($I);
        $this->grabOtpFromEmail($I);
        $I->deleteAllEmails();

        // Push the last-sent stamp past the 60s cooldown so a resend is allowed deterministically.
        $I->updateInDatabase(
            'oesm_2fa_otp',
            ['LAST_SENT_AT' => date('Y-m-d H:i:s', time() - 120)],
            ['OXUSERID' => $user['userId']]
        );

        $I->amBearerAuthenticated($challenge);
        $data = $this->sendGraphQL($I, self::RESEND);

        $I->assertArrayNotHasKey('errors', $data, 'A resend past the cooldown must succeed');
        $I->assertTrue($data['data']['resendTwoFactorOtp'] ?? false);

        // A fresh 6-digit OTP email actually went out, and the challenge row still exists.
        $I->assertMatchesRegularExpression('/^\d{6}$/', $this->grabOtpFromEmail($I));
        $I->seeInDatabase('oesm_2fa_otp', ['OXUSERID' => $user['userId']]);
    }

    public function resendRejectsANonChallengeToken(AcceptanceTester $I): void
    {
        $this->prepareRegularUser($I);
        $fullToken = $this->requestChallengeToken($I);

        $I->amBearerAuthenticated($fullToken);
        $data = $this->sendGraphQL($I, self::RESEND);

        $I->assertArrayHasKey('errors', $data);
        $I->assertNull($data['data']['resendTwoFactorOtp'] ?? null);
        $I->assertStringContainsString('two-factor', strtolower($data['errors'][0]['message'] ?? ''));
    }

    private function requestChallengeToken(AcceptanceTester $I): string
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
}
