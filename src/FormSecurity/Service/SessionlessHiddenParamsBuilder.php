<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\FormSecurity\Service;

use OxidEsales\Eshop\Core\Registry;

class SessionlessHiddenParamsBuilder implements SessionlessHiddenParamsBuilderInterface
{
    public function build(string $additionalParams): string
    {
        $value = $this->getSidHiddenParameter();

        $lang = $this->getFormLang();
        if ($lang !== '') {
            $value .= "\n{$lang}";
        }

        $value .= $additionalParams;

        return $value;
    }

    /**
     * Emits the session id hidden input when the shop needs it (e.g. cookieless
     * sessions), mirroring the core Session::hiddenSid() output but WITHOUT the
     * stoken input. Dropping the stoken keeps it out of GET URLs; keeping the sid
     * preserves the session when cookies are disabled.
     */
    protected function getSidHiddenParameter(): string
    {
        $session = Registry::getSession();

        if (!$session->isSidNeeded()) {
            return '';
        }

        return '<input type="hidden" name="' . $session->getName() . '" value="' . $session->getId() . '" />';
    }

    protected function getFormLang(): string
    {
        return Registry::getLang()->getFormLang();
    }
}
