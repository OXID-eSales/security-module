<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use Exception;

class NotifierService implements NotifierServiceInterface
{
    private array $collectedNotifiers;

    public function __construct(
        private readonly iterable $notifiers,
    ) {
        $this->collectedNotifiers = iterator_to_array($this->notifiers);
    }

    public function notify(
        string $type,
        string $recipient,
        string $code
    ): void {
        $notifierFound = array_filter(
            $this->collectedNotifiers,
            fn ($notifier) => $notifier->getName() === $type
        );

        if (!$notifierFound) {
            throw new Exception();
        }

        $notifier = reset($this->collectedNotifiers);
        $notifier->notify($recipient, $code);
    }
}
