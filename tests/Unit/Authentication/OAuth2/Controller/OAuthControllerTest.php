<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Controller;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\Authentication\OAuth2\Controller\OAuthController;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\AuthenticationServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Transput\OAuthRequestInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;
use PHPUnit\Framework\TestCase;

class OAuthControllerTest extends TestCase
{
    public function testLoginRedirectsToAuthorizationUrl(): void
    {
        $providerName = uniqid();
        $authorizationUrl = uniqid();

        $oauthRequestStub = $this->createStub(OAuthRequestInterface::class);
        $oauthRequestStub->method('getProvider')->willReturn($providerName);

        $authenticationServiceStub = $this->createStub(AuthenticationServiceInterface::class);
        $authenticationServiceStub->method('getAuthorizationUrl')
            ->with($providerName)
            ->willReturn($authorizationUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($authorizationUrl);

        $sut = $this->getSut(
            authenticationService: $authenticationServiceStub,
            oauthRequest: $oauthRequestStub,
            utils: $utilsSpy,
        );

        $sut->login();
    }

    public function testRedirectWithErrorSkipsCallbackAndRedirects(): void
    {
        $redirectUrl = uniqid();

        $oauthRequestStub = $this->createStub(OAuthRequestInterface::class);
        $oauthRequestStub->method('hasError')->willReturn(true);

        $authenticationServiceSpy = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationServiceSpy->expects($this->never())
            ->method('handleCallback');

        $redirectServiceStub = $this->createStub(InternalRedirectServiceInterface::class);
        $redirectServiceStub->method('getRedirectUrl')->willReturn($redirectUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($redirectUrl, false);

        $sut = $this->getSut(
            authenticationService: $authenticationServiceSpy,
            oauthRequest: $oauthRequestStub,
            redirectService: $redirectServiceStub,
            utils: $utilsSpy,
        );

        $sut->redirect();
    }

    public function testRedirectHandlesCallbackAndRedirectsToInternalUrl(): void
    {
        $providerName = uniqid();
        $code = uniqid();
        $redirectUrl = uniqid();

        $oauthRequestStub = $this->createStub(OAuthRequestInterface::class);
        $oauthRequestStub->method('getProvider')->willReturn($providerName);
        $oauthRequestStub->method('getCode')->willReturn($code);

        $authenticationServiceSpy = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationServiceSpy->expects($this->once())
            ->method('handleCallback')
            ->with($providerName, $code);

        $redirectServiceStub = $this->createStub(InternalRedirectServiceInterface::class);
        $redirectServiceStub->method('getRedirectUrl')->willReturn($redirectUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($redirectUrl, false);

        $sut = $this->getSut(
            authenticationService: $authenticationServiceSpy,
            oauthRequest: $oauthRequestStub,
            redirectService: $redirectServiceStub,
            utils: $utilsSpy,
        );

        $sut->redirect();
    }

    private function getSut(
        AuthenticationServiceInterface $authenticationService = null,
        OAuthRequestInterface $oauthRequest = null,
        InternalRedirectServiceInterface $redirectService = null,
        Utils $utils = null,
    ): OAuthController {
        return new OAuthController(
            authService: $authenticationService ?? $this->createStub(AuthenticationServiceInterface::class),
            oauthRequest: $oauthRequest ?? $this->createStub(OAuthRequestInterface::class),
            redirectService: $redirectService ?? $this->createStub(InternalRedirectServiceInterface::class),
            utils: $utils ?? $this->createStub(Utils::class),
        );
    }
}
