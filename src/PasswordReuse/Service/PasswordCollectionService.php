<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\Service;

use OxidEsales\SecurityModule\PasswordReuse\Exception\PasswordReuseCheckException;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Hashing\PasswordHasherInterface;
use Throwable;

class PasswordCollectionService implements PasswordCollectionServiceInterface
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
        private PasswordCollectionBuilderInterface $collectionBuilder,
    ) {
    }

    public function isCandidateInCollection(
        string $userId,
        #[\SensitiveParameter] string $candidate,
        string $currentHash,
    ): bool {
        try {
            foreach ($this->collectionBuilder->build($userId, $currentHash) as $member) {
                if ($this->passwordHasher->verifyPassword($candidate, $member)) {
                    return true;
                }
            }

            return false;
        } catch (Throwable $exception) {
            throw new PasswordReuseCheckException($exception);
        }
    }
}
