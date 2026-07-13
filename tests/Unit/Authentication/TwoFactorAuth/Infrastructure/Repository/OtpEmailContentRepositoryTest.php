<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\ContentModelFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpEmailContentRepositoryTest extends TestCase
{
    #[Test]
    public function getEmailSubjectReturnsTitleForActiveContent(): void
    {
        $title = uniqid();
        $contentStub = $this->createStub(Content::class);
        $contentStub->method('loadByIdent')->willReturn(true);
        $contentStub->method('getFieldData')->willReturn($title);

        $sut = $this->getSut($this->factoryReturning($contentStub));

        $this->assertSame($title, $sut->getEmailSubject(uniqid()));
    }

    #[Test]
    public function getEmailSubjectReturnsNullWhenNoActiveContentLoads(): void
    {
        $contentStub = $this->createStub(Content::class);
        $contentStub->method('loadByIdent')->willReturn(false);

        $sut = $this->getSut($this->factoryReturning($contentStub));

        $this->assertNull($sut->getEmailSubject(uniqid()));
    }

    #[Test]
    public function getEmailSubjectReturnsNullWhenTitleIsEmpty(): void
    {
        $contentStub = $this->createStub(Content::class);
        $contentStub->method('loadByIdent')->willReturn(true);
        $contentStub->method('getFieldData')->willReturn('   ');

        $sut = $this->getSut($this->factoryReturning($contentStub));

        $this->assertNull($sut->getEmailSubject(uniqid()));
    }

    private function factoryReturning(Content $content): ContentModelFactoryInterface
    {
        $factoryStub = $this->createStub(ContentModelFactoryInterface::class);
        $factoryStub->method('create')->willReturn($content);

        return $factoryStub;
    }

    private function getSut(?ContentModelFactoryInterface $contentFactory = null): OtpEmailContentRepository
    {
        return new OtpEmailContentRepository(
            contentFactory: $contentFactory ?? $this->createStub(ContentModelFactoryInterface::class),
        );
    }
}
