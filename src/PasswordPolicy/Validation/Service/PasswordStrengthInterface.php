<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface PasswordStrengthInterface
{
    public function estimateStrength(string $password): int;

    public function hasControlChar(int $charCode): bool;

    public function hasDigit(int $charCode): bool;

    public function hasUpperCase(int $charCode): bool;

    public function hasLowerCase(int $charCode): bool;

    public function hasSpecialChar(int $charCode): bool;

    public function hasOtherChar(int $charCode): bool;
}
