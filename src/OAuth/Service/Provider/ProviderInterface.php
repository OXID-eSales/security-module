<?php

namespace OxidEsales\SecurityModule\OAuth\Service\Provider;

interface ProviderInterface
{
    public function authenticate(): void;
}
