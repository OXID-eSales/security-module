<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Migration;

use Doctrine\DBAL\Schema\Schema;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailContent;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRenderer;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email\OtpEmailNotifier;
use OxidEsales\SecurityModule\Migrations\Version20260713120000;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;

class OtpMailContentMigrationTest extends IntegrationTestCase
{
    private const MAILPIT_API = 'http://mailpit:8025/api/v1';

    public function setUp(): void
    {
        parent::setUp();

        $this->applyMigration();
    }

    #[Test]
    public function migrationSeedsAnActiveOtpMailContentRow(): void
    {
        $row = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getRow(
            'SELECT OXACTIVE FROM oxcontents WHERE OXLOADID = ?',
            [OtpMailContent::IDENT]
        );

        $this->assertNotEmpty($row);
        $this->assertSame(1, (int) $row['OXACTIVE']);
    }

    #[Test]
    public function seededSubjectIsReadableThroughTheRepository(): void
    {
        $subject = $this->get(OtpEmailContentRepositoryInterface::class)
            ->getEmailSubject(OtpMailContent::IDENT);

        $this->assertContains($subject, ['Ihr Verifizierungscode', 'Your verification code']);
    }

    #[Test]
    public function notifierSendsCmsHtmlMailAfterSeeding(): void
    {
        $recipient = 'otp_' . substr(uniqid('', true), 0, 12) . '@example.test';
        $code = (string) random_int(100000, 999999);

        $this->buildNotifier($recipient)->notify(userId: uniqid(), code: $code);

        $message = $this->fetchDeliveredMessage($recipient);
        $this->assertNotNull($message, 'No email was delivered to the recipient.');
        $this->assertStringContainsString($code, (string) $message['HTML']);
    }

    private function applyMigration(): void
    {
        // The migration/ directory is loaded by the Doctrine migrations config, not composer PSR-4,
        // so the class must be required explicitly for this test.
        if (!class_exists(Version20260713120000::class, false)) {
            require_once __DIR__ . '/../../../migration/data/Version20260713120000.php';
        }

        $connection = ContainerFactory::getInstance()->getContainer()
            ->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        $migration = new Version20260713120000($connection, new NullLogger());
        $migration->up(new Schema());

        $db = DatabaseProvider::getDb();
        foreach ($migration->getSql() as $query) {
            $db->execute($query->getStatement());
        }
    }

    private function buildNotifier(string $recipient): OtpEmailNotifier
    {
        $userStub = $this->createStub(UserInterface::class);
        $userStub->method('getEmail')->willReturn($recipient);
        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserById')->willReturn($userStub);

        return new OtpEmailNotifier(
            emailFactory: new EmailFactory(),
            userRepository: $userRepositoryStub,
            shopAdapter: $this->get(ShopAdapterInterface::class),
            contentRepository: $this->get(OtpEmailContentRepositoryInterface::class),
            renderer: new OtpMailRenderer(
                ContainerFactory::getInstance()->getContainer()->get(TemplateRendererBridgeInterface::class),
            ),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchDeliveredMessage(string $recipient): ?array
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $list = $this->mailpitGet('/search?query=' . urlencode('to:' . $recipient));
            if (!empty($list['messages'])) {
                return $this->mailpitGet('/message/' . $list['messages'][0]['ID']);
            }
            usleep(100000);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function mailpitGet(string $path): array
    {
        $response = file_get_contents(self::MAILPIT_API . $path);

        return $response ? (array) json_decode($response, true) : [];
    }
}
