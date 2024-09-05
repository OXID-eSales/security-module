<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Controller;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\RequestInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Transput\ResponseInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrengthServiceInterface;

class PasswordAjaxController extends WidgetController
{
    public function passwordStrength(): void
    {
        $request = $this->getService(RequestInterface::class);
        $password = $request->getPassword();

        $passwordStrength = $this->getService(PasswordStrengthServiceInterface::class)
            ->estimateStrength($password);

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsJson([
            'strength' => $passwordStrength,
            'message' => Registry::getLang()->translateString('ERROR_PASSWORD_STRENGTH_' . $passwordStrength),
        ]);
    }
}
