<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\Infrastructure\Email;

use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRenderer;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRendererInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class OtpMailRendererTest extends IntegrationTestCase
{
    #[Test]
    #[DataProvider('otpTemplateProvider')]
    public function rendersOtpTemplateWithTheCodeFromCmsContent(string $template): void
    {
        $ident = 'otp_' . substr(uniqid('', true), 0, 20);
        $code = (string) random_int(100000, 999999);
        $this->seedContent($ident, 'Your code: {{ otp }}');

        $output = $this->getSut()->render(
            $template,
            ['otp' => $code, 'contentIdent' => $ident],
        );

        $this->assertStringContainsString($code, $output);
    }

    public static function otpTemplateProvider(): array
    {
        $namespace = '@' . Module::MODULE_ID;

        return [
            'plain' => ["$namespace/email/plain/twofactorotp.html.twig"],
            'html' => ["$namespace/email/html/twofactorotp.html.twig"],
        ];
    }

    private function seedContent(string $ident, string $body): void
    {
        $content = oxNew(Content::class);
        $content->assign([
            'oxloadid' => $ident,
            'oxactive' => 1,
            'oxshopid' => 1,
            'oxsnippet' => 1,
            'oxtype' => 0,
            'oxtitle' => 'Verification code',
            'oxcontent' => $body,
        ]);
        $content->save();
    }

    private function getSut(?TemplateRendererBridgeInterface $rendererBridge = null): OtpMailRendererInterface
    {
        return new OtpMailRenderer(
            rendererBridge: $rendererBridge
                ?? ContainerFactory::getInstance()->getContainer()->get(TemplateRendererBridgeInterface::class),
        );
    }
}
