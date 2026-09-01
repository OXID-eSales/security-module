<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\Service;

use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Repository\PasswordHistoryRepositoryInterface;

class PasswordCollectionBuilder implements PasswordCollectionBuilderInterface
{
    public function __construct(
        private PasswordHistoryRepositoryInterface $historyRepository,
        private ModuleSettingsServiceInterface $settings,
        private AccountTypeResolverInterface $accountTypeResolver,
    ) {
    }

    /**
     * @return list<string>
     */
    public function build(string $userId, string $currentHash): array
    {
        $previousEntriesBound = $this->resolveCollectionSize($userId) - 1;
        $previousHashes = $this->historyRepository->findRecentHashes($userId, $previousEntriesBound);

        return [$currentHash, ...$previousHashes];
    }

    private function resolveCollectionSize(string $userId): int
    {
        $rights = $this->accountTypeResolver->resolveAccount($userId)->getRights();

        return $this->settings->resolveCollectionSizeForRights($rights);
    }
}
