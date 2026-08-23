<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordReuse\Service;

use OxidEsales\SecurityModule\PasswordReuse\Exception\PasswordReuseCheckException;
use OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Hashing\PasswordHasherInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordCollectionBuilderInterface;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordCollectionService;
use OxidEsales\SecurityModule\PasswordReuse\Service\PasswordCollectionServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

class PasswordCollectionServiceTest extends TestCase
{
    #[Test]
    public function candidateMatchingCurrentHashIsInCollection(): void
    {
        $candidate = uniqid('plain_', true);
        $currentHash = uniqid('hash_', true);

        $hasherMock = $this->createMock(PasswordHasherInterface::class);
        $hasherMock->expects($this->once())
            ->method('verifyPassword')
            ->with($candidate, $currentHash)
            ->willReturn(true);

        $sut = $this->getSut(
            passwordHasher: $hasherMock,
            collectionBuilder: $this->builderReturning([$currentHash]),
        );

        $this->assertTrue($sut->isCandidateInCollection(uniqid('user_', true), $candidate, $currentHash));
    }

    #[Test]
    public function candidateMatchingAPreviousHashIsInCollection(): void
    {
        $candidate = uniqid('plain_', true);
        $currentHash = uniqid('hash_current_', true);
        $previousHashes = [uniqid('hash_prev1_', true), uniqid('hash_prev2_', true)];

        $hasherStub = $this->createStub(PasswordHasherInterface::class);
        $hasherStub->method('verifyPassword')->willReturnCallback(
            static fn(string $plain, string $hash): bool => $hash === $previousHashes[1]
        );

        $sut = $this->getSut(
            passwordHasher: $hasherStub,
            collectionBuilder: $this->builderReturning([$currentHash, ...$previousHashes]),
        );

        $this->assertTrue($sut->isCandidateInCollection(uniqid('user_', true), $candidate, $currentHash));
    }

    #[Test]
    public function candidateMatchingNoMemberIsNotInCollection(): void
    {
        $hasherStub = $this->createStub(PasswordHasherInterface::class);
        $hasherStub->method('verifyPassword')->willReturn(false);

        $sut = $this->getSut(
            passwordHasher: $hasherStub,
            collectionBuilder: $this->builderReturning([uniqid('hash_', true), uniqid('hash_prev_', true)]),
        );

        $this->assertFalse(
            $sut->isCandidateInCollection(uniqid('user_', true), uniqid('plain_', true), uniqid('hash_', true))
        );
    }

    #[Test]
    public function verificationUsesLegacyAwareHashPathNotStringEquality(): void
    {
        $candidate = 'secret-plaintext';
        $legacyCurrentHash = md5('secret-plaintext');

        $hasherMock = $this->createMock(PasswordHasherInterface::class);
        $hasherMock->expects($this->once())
            ->method('verifyPassword')
            ->with($candidate, $legacyCurrentHash)
            ->willReturn(true);

        $this->assertNotSame($candidate, $legacyCurrentHash);

        $sut = $this->getSut(
            passwordHasher: $hasherMock,
            collectionBuilder: $this->builderReturning([$legacyCurrentHash]),
        );

        $this->assertTrue($sut->isCandidateInCollection(uniqid('user_', true), $candidate, $legacyCurrentHash));
    }

    #[Test]
    public function builderFailureRaisesFailClosedException(): void
    {
        $builderStub = $this->createStub(PasswordCollectionBuilderInterface::class);
        $builderStub->method('build')->willThrowException(new RuntimeException('collection build failed'));

        $sut = $this->getSut(collectionBuilder: $builderStub);

        $this->expectException(PasswordReuseCheckException::class);

        $sut->isCandidateInCollection(uniqid('user_', true), uniqid('plain_', true), uniqid('hash_', true));
    }

    #[Test]
    public function verifyServiceFailureRaisesFailClosedException(): void
    {
        $hasherStub = $this->createStub(PasswordHasherInterface::class);
        $hasherStub->method('verifyPassword')->willThrowException(new RuntimeException('hash service unavailable'));

        $sut = $this->getSut(
            passwordHasher: $hasherStub,
            collectionBuilder: $this->builderReturning([uniqid('hash_', true)]),
        );

        $this->expectException(PasswordReuseCheckException::class);

        $sut->isCandidateInCollection(uniqid('user_', true), uniqid('plain_', true), uniqid('hash_', true));
    }

    #[Test]
    public function plaintextCandidateIsMarkedSensitiveToKeepItOutOfLogsAndTraces(): void
    {
        $parameters = (new ReflectionMethod(PasswordCollectionService::class, 'isCandidateInCollection'))
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
        $this->assertInstanceOf(PasswordCollectionServiceInterface::class, $this->getSut());
    }

    private function getSut(
        ?PasswordHasherInterface $passwordHasher = null,
        ?PasswordCollectionBuilderInterface $collectionBuilder = null,
    ): PasswordCollectionService {
        return new PasswordCollectionService(
            $passwordHasher ?? $this->createStub(PasswordHasherInterface::class),
            $collectionBuilder ?? $this->builderReturning([]),
        );
    }

    /**
     * @param list<string> $collection
     */
    private function builderReturning(array $collection): PasswordCollectionBuilderInterface
    {
        $builderStub = $this->createStub(PasswordCollectionBuilderInterface::class);
        $builderStub->method('build')->willReturn($collection);

        return $builderStub;
    }
}
