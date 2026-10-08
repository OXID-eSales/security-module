<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRenderer;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationMailSender;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationMailSenderInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationUrlBuilder;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactory;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;

class VerificationMailSenderTest extends IntegrationTestCase
{
    #[Test]
    public function sendEmailVerificationMailThrowsForAnUnknownUser(): void
    {
        $sut = $this->getSut();

        $this->expectException(VerificationMailNotSentException::class);

        $sut->sendEmailVerificationMail(userId: uniqid(), token: uniqid(), expiresAt: new DateTimeImmutable());
    }

    private function getSut(): VerificationMailSenderInterface
    {
        return new VerificationMailSender(
            userModelFactory: new UserModelFactory(),
            urlBuilder: new VerificationUrlBuilder(config: $this->createStub(Config::class)),
            emailFactory: new EmailFactory(),
            shopAdapter: $this->get(ShopAdapterInterface::class),
            contentRepository: $this->get(OtpEmailContentRepositoryInterface::class),
            renderer: new OtpMailRenderer(
                ContainerFactory::getInstance()->getContainer()->get(TemplateRendererBridgeInterface::class),
            ),
            logger: new NullLogger(),
        );
    }
}
