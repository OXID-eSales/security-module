<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsService;

trait SessionEvictionTestTrait
{
    private function runWithSessionEviction(bool $enabled, bool $strict, callable $test): void
    {
        $settingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $original = [
            ModuleSettingsService::SESSION_EVICTION_ENABLE => $settingService->getBoolean(
                ModuleSettingsService::SESSION_EVICTION_ENABLE,
                Module::MODULE_ID
            ),
            ModuleSettingsService::SESSION_EVICTION_STRICT => $settingService->getBoolean(
                ModuleSettingsService::SESSION_EVICTION_STRICT,
                Module::MODULE_ID
            ),
        ];

        $settingService->saveBoolean(ModuleSettingsService::SESSION_EVICTION_ENABLE, $enabled, Module::MODULE_ID);
        $settingService->saveBoolean(ModuleSettingsService::SESSION_EVICTION_STRICT, $strict, Module::MODULE_ID);

        try {
            $test();
        } finally {
            foreach ($original as $name => $value) {
                $settingService->saveBoolean($name, $value, Module::MODULE_ID);
            }
        }
    }

    private function createUser(): UserModel
    {
        $user = oxNew(UserModel::class);
        $user->setId(uniqid('userId'));
        $user->assign([
            'oxusername' => uniqid('userName') . '@example.test',
            'oxshopid' => Registry::getConfig()->getShopId(),
            'oxactive' => 1,
        ]);
        $user->save();
        $user->load($user->getId());

        return $user;
    }

    private function createUserWithUpdateId(): UserModel
    {
        $user = $this->createUser();
        $user->getUpdateId();

        return $user;
    }

    private function createArticle(): string
    {
        $article = oxNew(Article::class);
        $article->setId(uniqid('articleId'));
        $article->assign([
            'oxshopid' => Registry::getConfig()->getShopId(),
            'oxactive' => 1,
            'oxtitle' => uniqid('articleTitle'),
            'oxprice' => rand(1, 100),
            'oxstock' => rand(5, 50),
            'oxstockflag' => 1,
            'oxparentid' => '',
            'oxvarstock' => 0,
            'oxvarcount' => 0,
        ]);
        $article->save();
        $article->load($article->getId());

        return $article->getId();
    }

    private function sendRequest(array $parameters): void
    {
        $_POST = $parameters;
        Registry::set(Request::class, new Request());
    }

    private function startSession(?string $userId = null, ?string $loginToken = null): void
    {
        $session = $this->session();
        $session->remove('usr');
        $session->remove('login-token');

        if ($userId !== null) {
            $session->set('usr', $userId);
        }
        if ($loginToken !== null) {
            $session->set('login-token', $loginToken);
        }
    }

    private function nextRequestUserId(): ?string
    {
        $user = oxNew(UserModel::class);

        return $user->loadActiveUser() ? (string) $user->getId() : null;
    }

    private function session(): SessionInterface
    {
        return ContainerFacade::get(SessionInterface::class);
    }
}
