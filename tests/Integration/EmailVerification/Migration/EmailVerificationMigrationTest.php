<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\EmailVerification\Migration;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EmailVerificationMigrationTest extends IntegrationTestCase
{
    #[Test]
    public function userTableHasNullableVerificationDate(): void
    {
        $schemaManager = $this->get(QueryBuilderFactoryInterface::class)->create()->getConnection()->getSchemaManager();

        $column = $schemaManager->listTableColumns('oxuser')['oeemailverified'] ?? null;

        $this->assertNotNull($column);
        $this->assertSame('datetime', $column->getType()->getName());
        $this->assertFalse($column->getNotnull());
    }

    #[Test]
    public function pendingVerificationTableHasExpectedColumns(): void
    {
        $schemaManager = $this->get(QueryBuilderFactoryInterface::class)->create()->getConnection()->getSchemaManager();

        $columns = array_keys($schemaManager->listTableColumns('oesm_email_verification'));

        $this->assertEqualsCanonicalizing(
            ['oxuserid', 'token_hash', 'expires_at', 'send_count', 'last_sent_at', 'created_at'],
            $columns
        );
    }

    #[Test]
    public function pendingVerificationTableIsKeyedByUserAndHasUniqueTokenHash(): void
    {
        $schemaManager = $this->get(QueryBuilderFactoryInterface::class)->create()->getConnection()->getSchemaManager();

        $indexes = $schemaManager->listTableIndexes('oesm_email_verification');

        $this->assertSame(['OXUSERID'], $indexes['primary']->getColumns());
        $this->assertTrue($indexes['token_hash']->isUnique());
        $this->assertSame(['TOKEN_HASH'], $indexes['token_hash']->getColumns());
    }

    #[Test]
    #[DataProvider('mailContentLanguageDataProvider')]
    public function verificationMailContentIsSeededWithAllPlaceholders(string $titleField, string $contentField): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('OXACTIVE', 'OXFOLDER', $titleField . ' AS title', $contentField . ' AS content')
            ->from('oxcontents')
            ->where('OXLOADID = :ident')
            ->setParameter('ident', 'oesmemailverification');

        $content = $queryBuilder->execute()->fetchAssociative();

        $this->assertIsArray($content);
        $this->assertSame('1', (string)$content['OXACTIVE']);
        $this->assertSame('CMSFOLDER_EMAILS', $content['OXFOLDER']);
        $this->assertNotEmpty($content['title']);
        foreach (['{{ userName }}', '{{ shopName }}', '{{ verificationLink }}', '{{ expiresAt }}'] as $placeholder) {
            $this->assertStringContainsString($placeholder, $content['content']);
        }
    }

    public static function mailContentLanguageDataProvider(): array
    {
        return [
            'german' => ['OXTITLE', 'OXCONTENT'],
            'english' => ['OXTITLE_1', 'OXCONTENT_1'],
        ];
    }
}
