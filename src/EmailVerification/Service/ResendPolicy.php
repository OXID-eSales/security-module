<?php

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;

class ResendPolicy implements ResendPolicyInterface
{
    private const FREE_SENDS = 3;

    private const COOLDOWN_TIERS_SECONDS = [300, 900, 3600];

    public function getCooldownRemaining(PendingVerificationDtoInterface $pendingVerification): int
    {
        $cooldown = $this->getCooldownSeconds($pendingVerification->getSendCount());
        if ($cooldown === 0) {
            return 0;
        }

        $elapsed = (new DateTimeImmutable())->getTimestamp()
            - $pendingVerification->getLastSentAt()->getTimestamp();

        return max(0, $cooldown - $elapsed);
    }

    private function getCooldownSeconds(int $sendCount): int
    {
        if ($sendCount < self::FREE_SENDS) {
            return 0;
        }

        $tier = min($sendCount - self::FREE_SENDS, count(self::COOLDOWN_TIERS_SECONDS) - 1);

        return self::COOLDOWN_TIERS_SECONDS[$tier];
    }
}
