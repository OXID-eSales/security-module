<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface PasswordStrengthServiceInterface
{
    public function estimateStrength(string $password): int;
}
