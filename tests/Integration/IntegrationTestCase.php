<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Tests\Integration;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase as EshopIntegrationTestCase;
use OxidEsales\Facts\Facts;

class IntegrationTestCase extends EshopIntegrationTestCase
{
    public function setUp(): void
    {
        $facts = new Facts();

        $container = ContainerFactory::getInstance()->getContainer();
        $connection = $container->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        $connection->executeStatement(
            file_get_contents(
                __DIR__ . '/../Fixtures/testdata_'
                . strtolower($facts->getEdition()) . '.sql'
            )
        );

        parent::setUp();
    }
}
