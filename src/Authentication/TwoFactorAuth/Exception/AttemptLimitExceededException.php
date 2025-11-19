<?php

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class AttemptLimitExceededException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_ATTEMPT_LIMIT_EXCEEDED');
    }
}
