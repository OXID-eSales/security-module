<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\Subscriber;

use Exception;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Application\Controller\FrontendController;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ViewRenderedEvent;
use OxidEsales\SecurityModule\Authentication\Session\SessionKeys;
use OxidEsales\SecurityModule\Authentication\Subscriber\StoreCurrentUrlSubscriber;
use PHPUnit\Framework\TestCase;

class StoreCurrentUrlSubscriberTest extends TestCase
{
    public function testGetSubscribedEventsReturnsViewRenderedEvent(): void
    {
        $events = StoreCurrentUrlSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(ViewRenderedEvent::class, $events);
        $this->assertSame('onViewRendered', $events[ViewRenderedEvent::class]);
    }

    public function testOnViewRenderedStoresCurrentUrl(): void
    {
        $currentUrl = uniqid();

        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('details');
        $viewStub->method('getLink')->willReturn($currentUrl);

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())
            ->method('set')
            ->with(SessionKeys::AUTH_REDIRECT_URL, $currentUrl);

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenAdmin(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(true);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWidgetControllers(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('oxwArticleBox');

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsTwoFactorAuthController(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('twofactorauth');

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsOAuthController(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('oauth');

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenNoActiveView(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn(null);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenGetLinkThrowsException(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('details');
        $viewStub->method('getLink')->willThrowException(new Exception('Link error'));

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsLogoutFunction(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('start');

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('logout');

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsLogoutFunctionCaseInsensitive(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('start');

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('Logout');

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedStoresUrlWithOtherFunction(): void
    {
        $currentUrl = uniqid();

        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('details');
        $viewStub->method('getLink')->willReturn($currentUrl);

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('tobasket');

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())
            ->method('set')
            ->with(SessionKeys::AUTH_REDIRECT_URL, $currentUrl);

        $sut = $this->getSut(
            session: $sessionMock,
            config: $configStub,
            request: $requestStub,
        );
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    private function getSut(
        SessionInterface $session = null,
        Config $config = null,
        Request $request = null,
    ): StoreCurrentUrlSubscriber {
        return new StoreCurrentUrlSubscriber(
            session: $session ?? $this->createStub(SessionInterface::class),
            config: $config ?? $this->createStub(Config::class),
            request: $request ?? $this->createStub(Request::class),
        );
    }
}
