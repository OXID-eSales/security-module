<?php

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;

interface OTPGeneratorInterface
{
    public function generate(UserModel $user): User;
}
