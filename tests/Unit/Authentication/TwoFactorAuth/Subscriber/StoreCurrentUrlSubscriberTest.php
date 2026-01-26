<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Subscriber;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Application\Controller\FrontendController;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ViewRenderedEvent;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Subscriber\StoreCurrentUrlSubscriber;
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
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())
            ->method('setVariable')
            ->with(AuthorizeService::OTP_TARGET_URL, $currentUrl);
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenAdmin(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(true);
        Registry::set(Config::class, $configStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
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
        Registry::set(Config::class, $configStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
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
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenNoActiveView(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn(null);
        Registry::set(Config::class, $configStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }

    public function testOnViewRenderedSkipsWhenGetLinkThrowsException(): void
    {
        $viewStub = $this->createStub(FrontendController::class);
        $viewStub->method('getClassKey')->willReturn('details');
        $viewStub->method('getLink')->willThrowException(new \Exception('Link error'));

        $configStub = $this->createStub(Config::class);
        $configStub->method('isAdmin')->willReturn(false);
        $configStub->method('getTopActiveView')->willReturn($viewStub);
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn(null);
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
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
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('logout');
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
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
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('Logout');
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->never())->method('setVariable');
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
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
        Registry::set(Config::class, $configStub);

        $requestStub = $this->createStub(Request::class);
        $requestStub->method('getRequestParameter')->with('fnc')->willReturn('tobasket');
        Registry::set(Request::class, $requestStub);

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())
            ->method('setVariable')
            ->with(AuthorizeService::OTP_TARGET_URL, $currentUrl);
        Registry::set(Session::class, $sessionMock);

        $sut = new StoreCurrentUrlSubscriber();
        $eventStub = $this->createStub(ViewRenderedEvent::class);

        $sut->onViewRendered($eventStub);
    }
}
