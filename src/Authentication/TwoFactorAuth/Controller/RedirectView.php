<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;

class RedirectView extends FrontendController
{
    private string $redirectUrl;

    public function setRedirectUrl(string $url): void
    {
        $this->redirectUrl = $url;
    }

    public function getLink($iLang = null)
    {
        return $this->redirectUrl;
    }
}
