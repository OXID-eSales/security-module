<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordReuse\Service;

use OxidEsales\SecurityModule\PasswordReuse\Exception\PasswordReuseCheckException;
use OxidEsales\SecurityModule\PasswordReuse\Exception\PasswordReuseException;
use OxidEsales\SecurityModule\PasswordReuse\Service\ConfirmedChangeRegistryInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordCollectionServiceInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordReuseGuardService;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordReuseGuardServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class PasswordReuseGuardServiceTest extends TestCase
{
    #[Test]
    public function disabledReusePreventionIsANoOpWithoutCheckingOrConfirming(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('isReusePreventionEnabled')->willReturn(false);

        $collectionSpy = $this->createMock(PasswordCollectionServiceInterface::class);
        $collectionSpy->expects($this->never())->method('isCandidateInCollection');

        $registrySpy = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registrySpy->expects($this->never())->method('confirm');

        $sut = $this->getSut(settings: $settingsStub, collection: $collectionSpy, registry: $registrySpy);

        $sut->guardChange(uniqid('user_', true), uniqid('plain_', true), uniqid('hash_', true));
    }

    #[Test]
    public function emptyCurrentHashIsInitialEstablishmentExemptFromCheckAndConfirm(): void
    {
        $collectionSpy = $this->createMock(PasswordCollectionServiceInterface::class);
        $collectionSpy->expects($this->never())->method('isCandidateInCollection');

        $registrySpy = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registrySpy->expects($this->never())->method('confirm');

        $sut = $this->getSut(collection: $collectionSpy, registry: $registrySpy);

        $sut->guardChange(uniqid('user_', true), uniqid('plain_', true), '');
    }

    #[Test]
    public function reusedCandidateIsRejectedAndNotConfirmed(): void
    {
        $userId = uniqid('user_', true);
        $candidate = uniqid('plain_', true);
        $currentHash = uniqid('hash_', true);

        $collectionMock = $this->createMock(PasswordCollectionServiceInterface::class);
        $collectionMock->expects($this->once())
            ->method('isCandidateInCollection')
            ->with($userId, $candidate, $currentHash)
            ->willReturn(true);

        $registrySpy = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registrySpy->expects($this->never())->method('confirm');

        $sut = $this->getSut(collection: $collectionMock, registry: $registrySpy);

        $this->expectException(PasswordReuseException::class);

        $sut->guardChange($userId, $candidate, $currentHash);
    }

    #[Test]
    public function allowedGenuineChangeConfirmsTheChangeSignal(): void
    {
        $userId = uniqid('user_', true);
        $candidate = uniqid('plain_', true);
        $currentHash = uniqid('hash_', true);

        $collectionStub = $this->createStub(PasswordCollectionServiceInterface::class);
        $collectionStub->method('isCandidateInCollection')->willReturn(false);

        $registrySpy = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registrySpy->expects($this->once())->method('confirm')->with($userId);

        $sut = $this->getSut(collection: $collectionStub, registry: $registrySpy);

        $sut->guardChange($userId, $candidate, $currentHash);
    }

    #[Test]
    public function failClosedCheckExceptionPropagatesLoggedAndDoesNotConfirm(): void
    {
        $collectionStub = $this->createStub(PasswordCollectionServiceInterface::class);
        $collectionStub->method('isCandidateInCollection')
            ->willThrowException(new PasswordReuseCheckException());

        $registrySpy = $this->createMock(ConfirmedChangeRegistryInterface::class);
        $registrySpy->expects($this->never())->method('confirm');

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())->method('error');

        $sut = $this->getSut(collection: $collectionStub, registry: $registrySpy, logger: $loggerSpy);

        $this->expectException(PasswordReuseCheckException::class);

        $sut->guardChange(uniqid('user_', true), uniqid('plain_', true), uniqid('hash_', true));
    }

    #[Test]
    public function plaintextCandidateIsMarkedSensitiveToKeepItOutOfLogsAndTraces(): void
    {
        $parameters = (new ReflectionMethod(PasswordReuseGuardService::class, 'guardChange'))
            ->getParameters();

        $candidateParameter = $parameters[1];

        $this->assertSame('candidate', $candidateParameter->getName());
        $this->assertNotEmpty(
            $candidateParameter->getAttributes(\SensitiveParameter::class),
            'The plaintext candidate must carry the SensitiveParameter attribute.'
        );
    }

    #[Test]
    public function implementsInterface(): void
    {
        $this->assertInstanceOf(PasswordReuseGuardServiceInterface::class, $this->getSut());
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?PasswordCollectionServiceInterface $collection = null,
        ?ConfirmedChangeRegistryInterface $registry = null,
        ?LoggerInterface $logger = null,
    ): PasswordReuseGuardService {
        if ($settings === null) {
            $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
            $settingsStub->method('isReusePreventionEnabled')->willReturn(true);
            $settings = $settingsStub;
        }

        return new PasswordReuseGuardService(
            $collection ?? $this->createStub(PasswordCollectionServiceInterface::class),
            $settings,
            $registry ?? $this->createStub(ConfirmedChangeRegistryInterface::class),
            $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
