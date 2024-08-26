<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface CharacterAnalysisInterface
{
    public function isControlChar(int $charCode): bool;

    public function isDigit(int $charCode): bool;

    public function isUpperCase(int $charCode): bool;

    public function isLowerCase(int $charCode): bool;

    public function isSpecialChar(int $charCode): bool;

    public function isOtherChar(int $charCode): bool;
}
