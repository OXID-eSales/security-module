<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\Authentication\TwoFactorAuth\Controller;

use Lcobucci\JWT\UnencryptedToken;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Base\DataType\LoginInterface;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Infrastructure\Legacy;
use OxidEsales\GraphQL\Base\Service\RefreshTokenServiceInterface;
use OxidEsales\GraphQL\Base\Service\Token;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAResendableInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Controller\TwoFactorAuthController;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception\TwoFactorChallengeException;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception\TwoFactorResendCooldownException;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service\ChallengeTokenValidatorServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthControllerTest extends TestCase
{
    #[Test]
    public function verifyTwoFactorTokenReturnsAccessTokenAndConsumesChallengeOnValidOtp(): void
    {
        $userId = uniqid();
        $otp = uniqid();
        $accessToken = uniqid();

        $tokenStub = $this->createStub(Token::class);
        $tokenStub->method('createTokenForUser')->willReturn($this->accessTokenStub($accessToken));

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())->method('verify')->with($userId, $otp);
        $twoFAServiceSpy->expects($this->once())->method('consumeChallenge')->with($userId);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            challengeValidator: $this->validatorReturning($userId),
            tokenService: $tokenStub,
            legacy: $this->legacyStubFor($userId),
        );

        $this->assertSame($accessToken, $sut->verifyTwoFactorToken($otp));
    }

    #[Test]
    public function verifyTwoFactorTokenTranslatesOtpFailureToClientAwareErrorAndKeepsChallenge(): void
    {
        $userId = uniqid();

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->method('verify')->willThrowException(new InvalidCodeException());
        $twoFAServiceSpy->expects($this->never())->method('consumeChallenge');

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            challengeValidator: $this->validatorReturning($userId),
            legacy: $this->legacyStubFor($userId),
        );

        $this->expectException(TwoFactorChallengeException::class);

        $sut->verifyTwoFactorToken(uniqid());
    }

    #[Test]
    public function verifyTwoFactorTokenPropagatesChallengeValidationFailureAndSkipsVerify(): void
    {
        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->never())->method('verify');
        $twoFAServiceSpy->expects($this->never())->method('consumeChallenge');

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            challengeValidator: $this->validatorThrowing(),
        );

        $this->expectException(InvalidToken::class);

        $sut->verifyTwoFactorToken(uniqid());
    }

    #[Test]
    public function verifyTwoFactorLoginReturnsAccessAndRefreshTokensAndConsumesChallenge(): void
    {
        $userId = uniqid();
        $otp = uniqid();
        $accessToken = uniqid();
        $refreshToken = uniqid();

        $tokenStub = $this->createStub(Token::class);
        $tokenStub->method('createTokenForUser')->willReturn($this->accessTokenStub($accessToken));

        $refreshServiceMock = $this->createMock(RefreshTokenServiceInterface::class);
        $refreshServiceMock->expects($this->once())
            ->method('createRefreshTokenForUser')
            ->willReturn($refreshToken);

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())->method('verify')->with($userId, $otp);
        $twoFAServiceSpy->expects($this->once())->method('consumeChallenge')->with($userId);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            challengeValidator: $this->validatorReturning($userId),
            tokenService: $tokenStub,
            legacy: $this->legacyStubFor($userId),
            refreshTokenService: $refreshServiceMock,
        );

        $result = $sut->verifyTwoFactorLogin($otp);

        $this->assertInstanceOf(LoginInterface::class, $result);
        $this->assertSame($accessToken, $result->accessToken());
        $this->assertSame($refreshToken, $result->refreshToken());
    }

    #[Test]
    public function verifyTwoFactorLoginPropagatesChallengeValidationFailureAndIssuesNoRefreshToken(): void
    {
        $refreshServiceSpy = $this->createMock(RefreshTokenServiceInterface::class);
        $refreshServiceSpy->expects($this->never())->method('createRefreshTokenForUser');

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->never())->method('verify');

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            challengeValidator: $this->validatorThrowing(),
            refreshTokenService: $refreshServiceSpy,
        );

        $this->expectException(InvalidToken::class);

        $sut->verifyTwoFactorLogin(uniqid());
    }

    #[Test]
    public function resendTwoFactorOtpTriggersResendForValidatedChallenge(): void
    {
        $userId = uniqid();

        $resendServiceSpy = $this->createMock(TwoFAResendableInterface::class);
        $resendServiceSpy->expects($this->once())->method('resend')->with($userId);

        $sut = $this->getSut(
            challengeValidator: $this->validatorReturning($userId),
            resendService: $resendServiceSpy,
        );

        $this->assertTrue($sut->resendTwoFactorOtp());
    }

    #[Test]
    public function resendTwoFactorOtpTranslatesCooldownToClientAwareError(): void
    {
        $resendServiceStub = $this->createStub(TwoFAResendableInterface::class);
        $resendServiceStub->method('resend')->willThrowException(new ResendCooldownException());

        $sut = $this->getSut(
            challengeValidator: $this->validatorReturning(uniqid()),
            resendService: $resendServiceStub,
        );

        $this->expectException(TwoFactorResendCooldownException::class);

        $sut->resendTwoFactorOtp();
    }

    #[Test]
    public function resendTwoFactorOtpPropagatesChallengeValidationFailureAndSkipsResend(): void
    {
        $resendServiceSpy = $this->createMock(TwoFAResendableInterface::class);
        $resendServiceSpy->expects($this->never())->method('resend');

        $sut = $this->getSut(
            challengeValidator: $this->validatorThrowing(),
            resendService: $resendServiceSpy,
        );

        $this->expectException(InvalidToken::class);

        $sut->resendTwoFactorOtp();
    }

    private function accessTokenStub(string $accessToken): UnencryptedToken
    {
        $stub = $this->createStub(UnencryptedToken::class);
        $stub->method('toString')->willReturn($accessToken);

        return $stub;
    }

    private function legacyStubFor(string $userId): Legacy
    {
        $userModelStub = $this->createStub(EshopUserModel::class);
        $userModelStub->method('getId')->willReturn($userId);

        $legacyStub = $this->createStub(Legacy::class);
        $legacyStub->method('getUserModel')->willReturn($userModelStub);

        return $legacyStub;
    }

    private function validatorReturning(string $userId): ChallengeTokenValidatorServiceInterface
    {
        $stub = $this->createStub(ChallengeTokenValidatorServiceInterface::class);
        $stub->method('validateAndGetUserId')->willReturn($userId);

        return $stub;
    }

    private function validatorThrowing(): ChallengeTokenValidatorServiceInterface
    {
        $stub = $this->createStub(ChallengeTokenValidatorServiceInterface::class);
        $stub->method('validateAndGetUserId')->willThrowException(new InvalidToken('challenge invalid'));

        return $stub;
    }

    private function getSut(
        ?TwoFAServiceInterface $twoFAService = null,
        ?ChallengeTokenValidatorServiceInterface $challengeValidator = null,
        ?TwoFAResendableInterface $resendService = null,
        ?Token $tokenService = null,
        ?Legacy $legacy = null,
        ?RefreshTokenServiceInterface $refreshTokenService = null,
    ): TwoFactorAuthController {
        return new TwoFactorAuthController(
            twoFAService: $twoFAService ?? $this->createStub(TwoFAServiceInterface::class),
            challengeValidator: $challengeValidator
                ?? $this->createStub(ChallengeTokenValidatorServiceInterface::class),
            resendService: $resendService ?? $this->createStub(TwoFAResendableInterface::class),
            tokenService: $tokenService ?? $this->createStub(Token::class),
            legacy: $legacy ?? $this->createStub(Legacy::class),
            refreshTokenService: $refreshTokenService ?? $this->createStub(RefreshTokenServiceInterface::class),
        );
    }
}
