<?php

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider;

interface NotifierAdapterInterface
{
    public function getName(): string;

    public function notify(string $recipient, string $code): void;
}
