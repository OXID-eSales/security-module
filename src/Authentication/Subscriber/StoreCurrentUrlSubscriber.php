<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\Subscriber;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ViewRenderedEvent;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class StoreCurrentUrlSubscriber implements EventSubscriberInterface
{
    private const EXCLUDED_CONTROLLERS = [
        'twofactorauth',
        'oauth',
    ];

    private const EXCLUDED_FUNCTIONS = [
        'logout',
    ];

    public function __construct(
        private SessionInterface $session,
        private Config $config,
        private Request $request
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewRenderedEvent::class => 'onViewRendered',
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onViewRendered(ViewRenderedEvent $event): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $currentController = $this->getCurrentController();
        $currentFunction = $this->getCurrentFunction();

        if (
            $this->isWidget($currentController)
            || $this->shouldExcludeController($currentController)
            || $this->shouldExcludeFunction($currentFunction)
        ) {
            return;
        }

        $currentUrl = $this->getCurrentPageUrl();
        if ($currentUrl) {
            $this->session->set(InternalRedirectService::AUTH_REDIRECT_URL, $currentUrl);
        }
    }

    private function isAdmin(): bool
    {
        return $this->config->isAdmin();
    }

    private function getCurrentController(): string
    {
        $activeView = $this->config->getTopActiveView();
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
        return strtolower((string) $this->request->getRequestParameter('fnc'));
    }

    private function shouldExcludeFunction(string $function): bool
    {
        return in_array($function, self::EXCLUDED_FUNCTIONS, true);
    }

    private function getCurrentPageUrl(): ?string
    {
        $activeView = $this->config->getTopActiveView();
        try {
            return $activeView->getLink();
        } catch (\Throwable) {
            return null;
        }
    }
}
