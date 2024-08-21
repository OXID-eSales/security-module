<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface PasswordStrengthInterface
{
    public function estimateStrength(string $password): float;

    public function hasControlChar(int $char): bool;

    public function hasDigit(int $char): bool;

    public function hasUpperCase(int $char): bool;

    public function hasLowerCase(int $char): bool;

    public function hasSpecialChar(int $char): bool;

    public function hasOtherChar(int $char): bool;
}
