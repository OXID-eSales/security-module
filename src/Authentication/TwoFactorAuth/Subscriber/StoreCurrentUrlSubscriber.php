<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Subscriber;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ViewRenderedEvent;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StoreCurrentUrlSubscriber implements EventSubscriberInterface
{
    private const EXCLUDED_CONTROLLERS = [
        'twofactorauth',
    ];

    private const EXCLUDED_FUNCTIONS = [
        'logout',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            ViewRenderedEvent::class => 'onViewRendered',
        ];
    }

    public function onViewRendered(ViewRenderedEvent $event): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $currentController = $this->getCurrentController();
        $currentFunction = $this->getCurrentFunction();

        // Skip widgets, excluded controllers, and excluded functions (like logout)
        if (
            $this->isWidget($currentController)
            || $this->shouldExcludeController($currentController)
            || $this->shouldExcludeFunction($currentFunction)
        ) {
            return;
        }

        $currentUrl = $this->getCurrentPageUrl();
        if ($currentUrl) {
            Registry::getSession()->setVariable(AuthorizeService::OTP_TARGET_URL, $currentUrl);
        }
    }

    private function isAdmin(): bool
    {
        return Registry::getConfig()->isAdmin();
    }

    private function getCurrentController(): string
    {
        $activeView = Registry::getConfig()->getTopActiveView();
        if (!$activeView) {
            return 'start';
        }

        return strtolower($activeView->getClassKey() ?? 'start');
    }

    private function isWidget(string $controller): bool
    {
        return str_starts_with($controller, 'oxw');
    }

    private function shouldExcludeController(string $controller): bool
    {
        return in_array($controller, self::EXCLUDED_CONTROLLERS, true);
    }

    private function getCurrentFunction(): string
    {
        return strtolower((string) Registry::getRequest()->getRequestParameter('fnc'));
    }

    private function shouldExcludeFunction(string $function): bool
    {
        return in_array($function, self::EXCLUDED_FUNCTIONS, true);
    }

    private function getCurrentPageUrl(): ?string
    {
        $activeView = Registry::getConfig()->getTopActiveView();
        try {
            return $activeView->getLink();
        } catch (\Throwable) {
            return null;
        }
    }
}
