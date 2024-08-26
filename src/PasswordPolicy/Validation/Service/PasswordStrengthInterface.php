<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface PasswordStrengthInterface
{
    public function estimateStrength(string $password): int;
}
