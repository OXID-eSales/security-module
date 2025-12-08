<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\VerificatorNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;

class VerificationCollectorService implements VerificationCollectorServiceInterface
{
    private readonly array $collectedVerificators;

    /**
     * @param iterable<VerificatorAdapterInterface> $verificators
     */
    public function __construct(
        protected iterable $verificators,
    ) {
        $this->collectedVerificators = iterator_to_array($this->verificators, false);
    }

    public function getVerificator(string $name): VerificatorAdapterInterface
    {
        $verificatorFound = array_filter(
            $this->collectedVerificators,
            fn ($verificator) => $verificator->getName() === $name
        );

        if (!$verificatorFound) {
            throw new VerificatorNotFoundException();
        }

        return reset($verificatorFound);
    }
}
