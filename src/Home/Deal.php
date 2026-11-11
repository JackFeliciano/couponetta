<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * One offer. The normal price and the deal price are both in cents, the share is in basis
 * points, and the two dates say when buying stops and when the coupon stops being usable.
 */
final class Deal
{
    public function __construct(
        public readonly string $handle,
        public readonly string $business,
        public readonly string $title,
        public readonly int $normalCents,
        public readonly int $dealCents,
        public readonly int $onSale,
        public readonly int $shareBp,
        public readonly string $buyByDay,
        public readonly string $useByDay,
    ) {
        if (trim($handle) === '') {
            throw new \RuntimeException('a deal carries a handle');
        }
        if (trim($title) === '') {
            throw new \RuntimeException('a deal carries a title');
        }
        if ($normalCents <= 0 || $dealCents <= 0) {
            throw new \RuntimeException('a price is a positive number of cents');
        }
        if ($dealCents >= $normalCents) {
            throw new \RuntimeException('a deal price sits below the normal price');
        }
        if ($onSale <= 0) {
            throw new \RuntimeException('a deal puts at least one coupon on sale');
        }
        if ($shareBp < 0 || $shareBp > 5000) {
            throw new \RuntimeException('the platform takes at most half of the deal price');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $buyByDay) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $useByDay)) {
            throw new \RuntimeException('a deal carries its days as 2026-01-31');
        }
        if ($useByDay <= $buyByDay) {
            throw new \RuntimeException('a coupon is usable after buying closes');
        }
    }

    /** What a buyer saves against the normal price, in cents. */
    public function savingCents(): int
    {
        return $this->normalCents - $this->dealCents;
    }

    /** The saving as a whole percentage of the normal price. */
    public function savingPercent(): int
    {
        return intdiv($this->savingCents() * 100, $this->normalCents);
    }

    /** What the business receives from one coupon. */
    public function payoutCents(): int
    {
        return $this->dealCents - intdiv($this->dealCents * $this->shareBp, 10000);
    }

    /** What the platform keeps from one coupon. */
    public function commissionCents(): int
    {
        return $this->dealCents - $this->payoutCents();
    }

    public function buyingOpenOn(string $today): bool
    {
        return $today <= $this->buyByDay;
    }
}
