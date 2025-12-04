<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\NotifierNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;

class NotifierCollector implements NotifierCollectorInterface
{
    private readonly array $collectedNotifiers;

    /**
     * @param iterable<NotifierAdapterInterface> $notifiers
     */
    public function __construct(
        protected iterable $notifiers,
    ) {
        $this->collectedNotifiers = iterator_to_array($this->notifiers, false);
    }

    public function getNotifier(string $name): NotifierAdapterInterface
    {
        $notifierFound = array_filter(
            $this->collectedNotifiers,
            fn ($notifier) => $notifier->getName() === $name
        );

        if (!$notifierFound) {
            throw new NotifierNotFoundException();
        }

        return reset($notifierFound);
    }
}
