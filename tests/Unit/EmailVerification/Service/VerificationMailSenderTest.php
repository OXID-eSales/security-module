<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use Closure;
use DateTimeImmutable;
use Generator;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRendererInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Email\EmailVerificationMailContent;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationMailSender;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationMailSenderInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationUrlBuilderInterface;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class VerificationMailSenderTest extends TestCase
{
    #[Test]
    public function sendEmailVerificationMail(): void
    {
        $email = uniqid() . '@example.com';
        $firstName = uniqid();
        $lastName = uniqid();
        $userName = $firstName . ' ' . $lastName;
        $token = uniqid();
        $link = uniqid();
        $shopName = uniqid();
        $shopEmail = uniqid() . '@example.com';
        $subject = uniqid();
        $dateFormat = uniqid();
        $expiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours');
        $html = uniqid() . $token;
        $plain = uniqid() . $token;

        $userStub = $this->createStub(User::class);
        $userStub->method('load')->willReturn(true);
        $userStub->method('getFieldData')->willReturnMap([
            ['oxusername', $email],
            ['oxfname', $firstName],
            ['oxlname', $lastName],
        ]);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userStub]);

        $urlBuilderMock = $this->createMock(VerificationUrlBuilderInterface::class);
        $urlBuilderMock->method('buildVerificationLink')->with($token)->willReturn($link);

        $shopAdapterMock = $this->createMock(ShopAdapterInterface::class);
        $shopAdapterMock->method('translateString')->with('EMAIL_VERIFICATION_DATE_FORMAT')->willReturn($dateFormat);

        $contentRepositoryMock = $this->createMock(OtpEmailContentRepositoryInterface::class);
        $contentRepositoryMock->method('getEmailSubject')
            ->with(EmailVerificationMailContent::IDENT)
            ->willReturn($subject);

        $expectedData = [
            'contentIdent' => EmailVerificationMailContent::IDENT,
            'userName' => $userName,
            'shopName' => $shopName,
            'verificationLink' => $link,
            'expiresAt' => $expiresAt->format($dateFormat),
            'subject' => $subject,
        ];
        $rendererSpy = $this->createMock(OtpMailRendererInterface::class);
        $rendererSpy->expects($this->exactly(2))
            ->method('render')
            ->with($this->stringContains('emailverification.html.twig'), $this->callback(
                fn(array $data): bool => array_intersect_assoc($expectedData, $data) === $expectedData
            ))
            ->willReturnCallback(
                fn(string $template): string => str_contains($template, '/html/') ? $html : $plain
            );

        $shopStub = $this->createStub(Shop::class);
        $shopStub->method('getFieldData')->willReturnMap([['oxname', $shopName], ['oxorderemail', $shopEmail]]);
        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->method('getShop')->willReturn($shopStub);
        $emailModelSpy->expects($this->once())->method('setFrom')->with($shopEmail, $shopName);
        $emailModelSpy->expects($this->once())->method('setSubject')->with($subject);
        $emailModelSpy->expects($this->once())->method('setBody')->with($html);
        $emailModelSpy->expects($this->once())->method('setAltBody')->with($plain);
        $emailModelSpy->expects($this->once())->method('setRecipient')->with($email, $userName);
        $emailModelSpy->expects($this->once())->method('send')->willReturn(true);
        $emailModelSpy->expects($this->never())->method('sendEmail');
        $emailFactoryStub = $this->createConfiguredStub(EmailFactoryInterface::class, ['create' => $emailModelSpy]);

        $sut = $this->getSut(
            userModelFactory: $userModelFactoryStub,
            urlBuilder: $urlBuilderMock,
            emailFactory: $emailFactoryStub,
            shopAdapter: $shopAdapterMock,
            contentRepository: $contentRepositoryMock,
            renderer: $rendererSpy,
        );

        $sut->sendEmailVerificationMail(userId: uniqid(), token: $token, expiresAt: $expiresAt);
    }

    #[Test]
    public function sendEmailVerificationMailUsesTheEmailAsUserNameWhenTheUserHasNoName(): void
    {
        $email = uniqid() . '@example.com';
        $token = uniqid();

        $userStub = $this->createStub(User::class);
        $userStub->method('load')->willReturn(true);
        $userStub->method('getFieldData')->willReturnMap([
            ['oxusername', $email],
            ['oxfname', ' '],
            ['oxlname', ''],
        ]);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userStub]);

        $contentRepositoryStub = $this->createConfiguredStub(OtpEmailContentRepositoryInterface::class, [
            'getEmailSubject' => uniqid(),
        ]);
        $rendererStub = $this->createConfiguredStub(OtpMailRendererInterface::class, ['render' => $token]);

        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->method('getShop')->willReturn($this->createStub(Shop::class));
        $emailModelSpy->method('send')->willReturn(true);
        $emailModelSpy->expects($this->once())->method('setRecipient')->with($email, $email);
        $emailFactoryStub = $this->createConfiguredStub(EmailFactoryInterface::class, ['create' => $emailModelSpy]);

        $sut = $this->getSut(
            userModelFactory: $userModelFactoryStub,
            emailFactory: $emailFactoryStub,
            contentRepository: $contentRepositoryStub,
            renderer: $rendererStub,
        );

        $sut->sendEmailVerificationMail(userId: uniqid(), token: $token, expiresAt: new DateTimeImmutable());
    }

    #[Test]
    public function sendEmailVerificationMailSendsBuiltInMailWhenCmsContentIsMissing(): void
    {
        $email = uniqid() . '@example.com';
        $firstName = uniqid();
        $lastName = uniqid();
        $link = uniqid();
        $shopName = uniqid();
        $subject = uniqid();
        $bodyTemplate = uniqid() . ' %1$s|%2$s|%3$s|%4$s';
        $dateFormat = uniqid();
        $expiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours');

        $userStub = $this->createStub(User::class);
        $userStub->method('load')->willReturn(true);
        $userStub->method('getFieldData')->willReturnMap([
            ['oxusername', $email],
            ['oxfname', $firstName],
            ['oxlname', $lastName],
        ]);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userStub]);

        $urlBuilderStub = $this->createConfiguredStub(VerificationUrlBuilderInterface::class, [
            'buildVerificationLink' => $link,
        ]);

        $shopAdapterStub = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterStub->method('translateString')->willReturnMap([
            ['EMAIL_VERIFICATION_DATE_FORMAT', $dateFormat],
            ['EMAIL_VERIFICATION_EMAIL_SUBJECT', $subject],
            ['EMAIL_VERIFICATION_EMAIL_BODY', $bodyTemplate],
        ]);

        $contentRepositoryStub = $this->createConfiguredStub(OtpEmailContentRepositoryInterface::class, [
            'getEmailSubject' => null,
        ]);

        $shopStub = $this->createStub(Shop::class);
        $shopStub->method('getFieldData')->willReturnMap([['oxname', $shopName]]);
        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->method('getShop')->willReturn($shopStub);
        $emailModelSpy->expects($this->never())->method('send');
        $emailModelSpy->expects($this->once())
            ->method('sendEmail')
            ->with(
                $email,
                $subject,
                sprintf($bodyTemplate, $firstName . ' ' . $lastName, $shopName, $link, $expiresAt->format($dateFormat))
            )
            ->willReturn(true);
        $emailFactoryStub = $this->createConfiguredStub(EmailFactoryInterface::class, ['create' => $emailModelSpy]);

        $sut = $this->getSut(
            userModelFactory: $userModelFactoryStub,
            urlBuilder: $urlBuilderStub,
            emailFactory: $emailFactoryStub,
            shopAdapter: $shopAdapterStub,
            contentRepository: $contentRepositoryStub,
        );

        $sut->sendEmailVerificationMail(userId: uniqid(), token: uniqid(), expiresAt: $expiresAt);
    }

    #[Test]
    #[DataProvider('cmsMailFailureDataProvider')]
    public function sendEmailVerificationMailFallsBackAndLogsWarningWhenTheCmsMailFails(
        Closure $render,
        bool $cmsMailSent,
        string $expectedWarning,
    ): void {
        $email = uniqid() . '@example.com';
        $token = uniqid();

        $userStub = $this->createStub(User::class);
        $userStub->method('load')->willReturn(true);
        $userStub->method('getFieldData')->willReturnMap([['oxusername', $email]]);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userStub]);

        $contentRepositoryStub = $this->createConfiguredStub(OtpEmailContentRepositoryInterface::class, [
            'getEmailSubject' => uniqid(),
        ]);
        $rendererStub = $this->createStub(OtpMailRendererInterface::class);
        $rendererStub->method('render')->willReturnCallback(fn(): string => $render($token));

        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->method('getShop')->willReturn($this->createStub(Shop::class));
        $emailModelSpy->method('send')->willReturn($cmsMailSent);
        $emailModelSpy->expects($this->once())
            ->method('sendEmail')
            ->with($email, $this->isString(), $this->isString())
            ->willReturn(true);
        $emailFactoryStub = $this->createConfiguredStub(EmailFactoryInterface::class, ['create' => $emailModelSpy]);

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('warning')
            ->with(
                $this->logicalAnd(
                    $this->stringContains($expectedWarning),
                    $this->logicalNot($this->stringContains($token))
                ),
                $this->arrayHasKey('contentIdent')
            );

        $sut = $this->getSut(
            userModelFactory: $userModelFactoryStub,
            emailFactory: $emailFactoryStub,
            contentRepository: $contentRepositoryStub,
            renderer: $rendererStub,
            logger: $loggerSpy,
        );

        $sut->sendEmailVerificationMail(userId: uniqid(), token: $token, expiresAt: new DateTimeImmutable());
    }

    public static function cmsMailFailureDataProvider(): Generator
    {
        yield 'renderer throws' => [
            'render' => static fn(string $token): string => throw new RuntimeException(uniqid()),
            'cmsMailSent' => true,
            'expectedWarning' => 'Rendering',
        ];
        yield 'rendered content lacks the link' => [
            'render' => static fn(string $token): string => uniqid(),
            'cmsMailSent' => true,
            'expectedWarning' => 'placeholder',
        ];
        yield 'sending the CMS mail fails' => [
            'render' => static fn(string $token): string => $token,
            'cmsMailSent' => false,
            'expectedWarning' => 'Sending',
        ];
    }

    #[Test]
    public function sendEmailVerificationMailThrowsWhenTheUserCannotBeLoaded(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->once())->method('load')->with($userId = uniqid())->willReturn(false);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userSpy]);

        $emailFactorySpy = $this->createMock(EmailFactoryInterface::class);
        $emailFactorySpy->expects($this->never())->method('create');

        $sut = $this->getSut(userModelFactory: $userModelFactoryStub, emailFactory: $emailFactorySpy);

        $this->expectException(VerificationMailNotSentException::class);

        $sut->sendEmailVerificationMail(userId: $userId, token: uniqid(), expiresAt: new DateTimeImmutable());
    }

    #[Test]
    public function sendEmailVerificationMailLogsErrorAndThrowsWhenCmsMailAndFallbackFail(): void
    {
        $userStub = $this->createConfiguredStub(User::class, ['load' => true]);
        $userModelFactoryStub = $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $userStub]);

        $contentRepositoryStub = $this->createConfiguredStub(OtpEmailContentRepositoryInterface::class, [
            'getEmailSubject' => null,
        ]);

        $emailModelStub = $this->createConfiguredStub(Email::class, [
            'getShop' => $this->createStub(Shop::class),
            'sendEmail' => false,
        ]);
        $emailFactoryStub = $this->createConfiguredStub(EmailFactoryInterface::class, ['create' => $emailModelStub]);

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('error')
            ->with($this->isString(), ['userId' => $userId = uniqid()]);

        $sut = $this->getSut(
            userModelFactory: $userModelFactoryStub,
            emailFactory: $emailFactoryStub,
            contentRepository: $contentRepositoryStub,
            logger: $loggerSpy,
        );

        $this->expectException(VerificationMailNotSentException::class);

        $sut->sendEmailVerificationMail(userId: $userId, token: uniqid(), expiresAt: new DateTimeImmutable());
    }

    private function getSut(
        ?UserModelFactoryInterface $userModelFactory = null,
        ?VerificationUrlBuilderInterface $urlBuilder = null,
        ?EmailFactoryInterface $emailFactory = null,
        ?ShopAdapterInterface $shopAdapter = null,
        ?OtpEmailContentRepositoryInterface $contentRepository = null,
        ?OtpMailRendererInterface $renderer = null,
        ?LoggerInterface $logger = null,
    ): VerificationMailSenderInterface {
        return new VerificationMailSender(
            userModelFactory: $userModelFactory ?? $this->createStub(UserModelFactoryInterface::class),
            urlBuilder: $urlBuilder ?? $this->createStub(VerificationUrlBuilderInterface::class),
            emailFactory: $emailFactory ?? $this->createStub(EmailFactoryInterface::class),
            shopAdapter: $shopAdapter ?? $this->createStub(ShopAdapterInterface::class),
            contentRepository: $contentRepository ?? $this->createStub(OtpEmailContentRepositoryInterface::class),
            renderer: $renderer ?? $this->createStub(OtpMailRendererInterface::class),
            logger: $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
