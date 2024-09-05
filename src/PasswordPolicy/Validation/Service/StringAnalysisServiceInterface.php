<?php

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

interface StringAnalysisServiceInterface
{
    public function hasUpperCaseCharacter(string $origin): bool;
}
