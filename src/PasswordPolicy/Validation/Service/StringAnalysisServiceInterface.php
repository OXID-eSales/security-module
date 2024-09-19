<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface StringAnalysisServiceInterface
{
    public function hasUpperCaseCharacter(string $origin): bool;

    public function hasLowerCaseCharacter(string $origin): bool;

    public function hasSpecialCharacter(string $origin): bool;

    public function hasDigitCharacter(string $origin): bool;
}
