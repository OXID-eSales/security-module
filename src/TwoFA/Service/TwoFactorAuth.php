<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\TwoFA\Service;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuth implements TwoFactorAuthInterface
{
    public function secretGenerate(): string
    {
        //todo: this should be unique per user and saved to user table for further usage
        $secret = 'AREG2CCNGHQSRFHS';

        return $secret;
    }

    public function QrUrlGenerate(string $username): string
    {
        $secret = $this->secretGenerate();

        $G2FA = new Google2FA();

        return $G2FA->getQRCodeUrl(
            'OxidEsales',
            $username,
            $secret
        );
    }

    public function codeVerify(): bool
    {
        return true;
    }

    public function QRCodeGenerate(string $username): string
    {
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            )
        );

        return $writer->writeString(
            $this->QrUrlGenerate(
                $username
            )
        );
    }
}
